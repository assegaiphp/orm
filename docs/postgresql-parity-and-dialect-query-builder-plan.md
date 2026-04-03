# PostgreSQL Parity And Dialect-Aware Query Builder Plan

## Goal

Bring PostgreSQL support up to the same practical level as MySQL across the ORM and CLI, then make the fluent query builder dialect-aware so it can emit database-specific SQL constructs without forcing every query through a MySQL-shaped API.

This plan is intentionally implementation-focused so we can pick it up next week without having to rediscover the shape of the work.

## Why This Work Matters

Right now PostgreSQL support exists in a few places, but it still feels secondary:

- the CLI database flow has historically favored MySQL
- the ORM has a `SQLDialect` enum and some dialect helpers, but much of the query-building surface is still generic or MySQL-leaning
- several SQL features that developers expect from PostgreSQL are not first-class yet
- there is not a clear split between:
  - portable SQL that works across dialects
  - dialect-specific constructs like `RETURNING`, `ILIKE`, `ON CONFLICT`, JSONB operators, or MySQL `INSERT IGNORE`

The result is that PostgreSQL works in some paths, but not with the same confidence or ergonomics as MySQL.

## Current State

### CLI

Relevant files:

- [console/src/Commands/Database/DatabaseSetup.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/console/src/Commands/Database/DatabaseSetup.php)
- [console/src/Core/Database/MySQLDatabase.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/console/src/Core/Database/MySQLDatabase.php)
- [console/src/Core/Database/PostgreSQLDatabase.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/console/src/Core/Database/PostgreSQLDatabase.php)

Observations:

- database setup was previously MySQL-first and PostgreSQL was effectively short-circuited
- shell-based database creation exists for both MySQL and PostgreSQL, but the flow is not yet unified or deeply tested
- driver-specific admin operations still live directly inside command support classes rather than behind a cleaner admin capability seam

### ORM Data Source And Schema

Relevant files:

- [orm/src/DataSource/DataSource.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/DataSource.php)
- [orm/src/DataSource/DBFactory.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/DBFactory.php)
- [orm/src/DataSource/Schema.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/Schema.php)
- [orm/src/Util/SqlDialectHelper.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Util/SqlDialectHelper.php)

Observations:

- there is already a useful dialect abstraction for identifier quoting and some schema behavior
- schema compilation and metadata loading already contain some PostgreSQL branches
- PostgreSQL support is still uneven across table-definition inspection, change compilation, and migration ergonomics

### Query Builder And SQL Composition

Relevant files:

- [orm/src/Queries/QueryBuilder/AbstractQueryBuilder.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/QueryBuilder/AbstractQueryBuilder.php)
- [orm/src/Queries/Sql/SQLQuery.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLQuery.php)
- [orm/src/Queries/Sql/SQLInsertIntoStatement.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLInsertIntoStatement.php)
- [orm/src/Queries/Sql/SQLUpdateDefinition.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLUpdateDefinition.php)
- [orm/src/Queries/Sql/SQLSelectDefinition.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLSelectDefinition.php)

Observations:

- the builder is centered on assembling SQL strings directly
- `SQLQuery` knows how to bind values, but not much about the active dialect
- the API surface does not currently make it obvious which methods are portable and which should be dialect-specific
- there is no first-class context-aware builder that says:
  - this is a PostgreSQL query, so `returning(...)`, `ilike(...)`, and `onConflict(...)` are available
  - this is a MySQL query, so `insertIgnore(...)`, `onDuplicateKeyUpdate(...)`, and MySQL-flavored limit/update constructs are available

## Definition Of Done

We should consider this effort successful when:

1. PostgreSQL database setup, schema sync, migrations, and common repository flows are as reliable as MySQL.
2. The query builder has a portable core API that works across dialects.
3. Dialect-specific builders expose richer methods only when the current connection/dialect supports them.
4. The ORM test suite runs meaningful MySQL, PostgreSQL, and SQLite coverage for the main data paths.
5. The documentation clearly teaches:
   - what is portable
   - what is dialect-specific
   - how to write PostgreSQL-first queries without dropping to raw SQL for common cases

## Guiding Principles

### 1. Portable core first

The default query-builder API should stay useful for:

- `select`
- `insert`
- `update`
- `delete`
- joins
- filtering
- grouping
- ordering
- limits

That gives developers one mental model for the common 80 percent.

### 2. Dialect-specific features should feel intentional

We should not pretend every database speaks the same SQL. Instead:

- keep a portable base builder
- add dialect-specific extensions only when the active dialect supports them

That means developers get expressive APIs without wondering whether a given method will silently break on another driver.

### 3. Context-aware means the connection decides the builder flavor

A `DataSource` or `EntityManager` should create the right builder for the current dialect. Developers should not have to manually choose `PostgreSqlSelectBuilder` in normal app code.

### 4. Avoid a breaking rewrite

The current builder and repository APIs are already used in apps. We should layer the dialect-aware path in gradually and preserve the existing core fluent surface where possible.

## Proposed Architecture

### A. Introduce a query dialect abstraction

Add a richer abstraction than `SQLDialect` alone:

- `SqlDialectInterface`
- `MySqlDialect`
- `PostgreSqlDialect`
- `SqliteDialect`

Responsibilities:

- quote identifiers
- normalize parameter markers if needed
- emit dialect-specific clauses
- expose capability checks

Suggested capabilities:

- supportsReturning
- supportsOnConflict
- supportsOnDuplicateKeyUpdate
- supportsInsertIgnore
- supportsIlike
- supportsJsonPathExpressions
- supportsForUpdateSkipLocked

This keeps dialect decisions out of random builder methods.

### B. Split builder responsibilities

Keep a portable base builder:

- `BaseSelectQueryBuilder`
- `BaseInsertQueryBuilder`
- `BaseUpdateQueryBuilder`
- `BaseDeleteQueryBuilder`

Then extend per dialect where needed:

- `MySqlSelectQueryBuilder`
- `PostgreSqlSelectQueryBuilder`
- `MySqlInsertQueryBuilder`
- `PostgreSqlInsertQueryBuilder`

The base builders should implement the portable API. Dialect builders should add only the extra surface for that database.

### C. Let DataSource or EntityManager return the correct builder

Add a builder factory that resolves from the connection dialect:

- `QueryBuilderFactory`

Example shape:

- `EntityManager->createQueryBuilder()` returns a builder family bound to the active dialect
- the returned builder is still typed as a common base, but can expose richer methods internally or through specialized accessors

Potential options:

1. Return concrete dialect builders directly.
2. Return portable builders with a `dialect()` extension object.

Recommended path:

- return concrete dialect builders directly from internal factories
- keep public typing broad enough to avoid unnecessary user friction

### D. Move dialect-specific SQL rendering out of statement classes

Today many statement classes concatenate SQL directly. Instead, introduce renderer classes:

- `InsertSqlRenderer`
- `SelectSqlRenderer`
- `UpdateSqlRenderer`
- `DeleteSqlRenderer`

Per dialect:

- `MySqlInsertSqlRenderer`
- `PostgreSqlInsertSqlRenderer`
- and so on

This keeps the builder focused on expressing intent while the renderer emits the final dialect-specific SQL.

### E. Preserve raw SQL escape hatches

Raw SQL should still be available, but the builder should cover the main dialect-specific features developers expect before they have to drop down to strings.

## PostgreSQL Parity Work Items

### CLI parity

1. Audit all database commands for PostgreSQL parity:
   - `database:configure`
   - `database:setup`
   - migration setup and listing flows
2. Move admin operations behind a shared capability layer:
   - create database
   - drop database
   - create migrations table
3. Add PostgreSQL-focused command tests, not just MySQL/SQLite assumptions.

### Data source parity

1. Audit `DataSource`, `DBFactory`, and `PostgreSQLDataSource` for feature differences from MySQL.
2. Ensure PostgreSQL-specific connection setup is consistent:
   - error handling
   - default search path assumptions
   - sequence / insert ID behavior
3. Review entity save/update/delete behavior with PostgreSQL as the primary driver.

### Schema parity

1. Audit generated column definitions across MySQL and PostgreSQL:
   - UUIDs
   - timestamps
   - booleans
   - enums
   - JSON / JSONB
2. Ensure schema diffing does not produce false positives across PostgreSQL metadata reads.
3. Confirm migration table creation and migration introspection behave the same way.

### Repository parity

1. Verify `save`, `find`, `findOne`, `update`, `softRemove`, and relations on PostgreSQL.
2. Close known gaps around:
   - insert result identity handling
   - upsert behavior
   - pagination/ordering edge cases
   - case-insensitive filters

## Dialect-Aware Query Builder Work Items

### Phase 1: establish the seam

1. Introduce `SqlDialectInterface` and concrete dialect classes.
2. Add a `QueryDialectContext` that carries:
   - dialect
   - connection
   - feature flags/capabilities
3. Update builder creation so the active connection chooses the dialect context.

### Phase 2: portable builder cleanup

1. Identify the portable core API.
2. Move current SQL string-building behavior behind a cleaner AST/expression map where necessary.
3. Keep existing method names where possible to reduce breakage.

### Phase 3: PostgreSQL-specific features

Add first-class PostgreSQL support for:

- `returning(...)`
- `onConflict(...)`
- `onConflictDoNothing()`
- `onConflictDoUpdate(...)`
- `ilike(...)`
- JSONB containment / path helpers
- `distinctOn(...)`
- lock clauses such as `forUpdate()` and `skipLocked()` where practical

### Phase 4: MySQL-specific features

Add first-class MySQL support for:

- `insertIgnore()`
- `onDuplicateKeyUpdate(...)`
- MySQL-flavored optimizer modifiers only where truly useful
- MySQL JSON helper expressions if we decide to expose them

### Phase 5: result handling parity

PostgreSQL often wants `RETURNING` instead of `lastInsertId()`. We should:

1. teach insert/update/delete flows to use `RETURNING` when the dialect supports it
2. stop assuming MySQL-style insert ID retrieval everywhere
3. let repository/service flows return the created/updated object cleanly regardless of dialect

## Testing Plan

### CLI tests

Add focused `console` tests for:

- MySQL setup SQL generation
- PostgreSQL setup SQL generation
- database setup command behavior for PostgreSQL paths where feasible

### ORM tests

Add a driver matrix for:

- MySQL
- PostgreSQL
- SQLite

Priority coverage:

1. create/save
2. update
3. soft delete / restore
4. relation loading
5. migrations list and setup
6. query builder SQL rendering snapshots
7. dialect-specific builder methods

### Snapshot-style SQL tests

For the new builder/renderers, add snapshot-like assertions such as:

- a portable insert emits correct SQL in MySQL and PostgreSQL
- PostgreSQL `returning(...)` renders only for PostgreSQL
- MySQL `insertIgnore()` renders only for MySQL

## Migration Strategy

### Step 1

Improve PostgreSQL parity without changing the public builder API much.

### Step 2

Introduce dialect context and renderer abstractions behind the scenes.

### Step 3

Add new dialect-specific fluent methods in a backwards-compatible way.

### Step 4

Deprecate or internally route any MySQL-only assumptions that currently appear generic.

## Risks

### Risk: builder complexity grows too quickly

Mitigation:

- keep the portable core small
- move rendering into per-dialect renderer classes

### Risk: hidden MySQL assumptions in repositories

Mitigation:

- add parity tests first
- use failing PostgreSQL tests to drive the gaps down method by method

### Risk: public API confusion

Mitigation:

- explicitly document which methods are portable and which are dialect-specific
- keep dialect-specific methods off the generic surface unless the active builder truly supports them

## Recommended Implementation Order For Next Week

1. Finish PostgreSQL parity audit and create failing tests for the biggest gaps.
2. Introduce dialect context and renderer interfaces.
3. Make insert flows dialect-aware first.
   This is the highest-value path because it unlocks `RETURNING`, better result handling, and future upsert parity.
4. Implement PostgreSQL `RETURNING` and MySQL `ON DUPLICATE KEY UPDATE` / `INSERT IGNORE`.
5. Extend select/update builders with `ILIKE`, `ON CONFLICT`, and other PostgreSQL features.
6. Expand docs and examples once the new fluent surface is real.

## First Concrete Deliverables

If we want a practical week-one target, the first deliverable should be:

- PostgreSQL setup and migration parity
- dialect context abstraction
- dialect-aware insert builder
- PostgreSQL `returning(...)`
- MySQL `insertIgnore(...)`
- SQL rendering tests for both dialects

That gets us from “some PostgreSQL support exists” to “the ORM has a real dialect-aware foundation.”
