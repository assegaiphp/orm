# MySQL Audit For 0.9.0

## Goal

Make MySQL the truly stable reference SQL dialect for Assegai ORM before we expand the parity work to SQLite and PostgreSQL.

This note is intentionally practical. It is meant to tell us:

- what already works well enough to keep
- what is risky or misleading today
- what we should fix first for `0.9.0`
- what should wait until the later SQLite and PostgreSQL passes

## What This Audit Covered

Main files reviewed:

- [orm/src/DataSource/MySQLDataSource.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/MySQLDataSource.php)
- [orm/src/DataSource/DataSource.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/DataSource.php)
- [orm/src/DataSource/DBFactory.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/DBFactory.php)
- [orm/src/DataSource/Schema.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/Schema.php)
- [orm/src/Management/EntityManager.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Management/EntityManager.php)
- [orm/src/Management/Repository.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Management/Repository.php)
- [orm/src/Management/DatabaseManager.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Management/DatabaseManager.php)
- [orm/src/Migrations/Migrator.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Migrations/Migrator.php)
- [orm/src/Queries/Sql/SQLQuery.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLQuery.php)
- [orm/src/Queries/Sql/SQLColumnDefinition.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLColumnDefinition.php)
- [orm/src/Traits/DuplicateKeyUpdatableTrait.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Traits/DuplicateKeyUpdatableTrait.php)

Tests reviewed:

- [orm/tests/Unit.suite.yml](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/tests/Unit.suite.yml)
- [orm/tests/Unit/SchemaCest.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/tests/Unit/SchemaCest.php)
- [orm/tests/Unit/DatabaseManagerCest.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/tests/Unit/DatabaseManagerCest.php)
- [orm/tests/Unit/MigratorCest.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/tests/Unit/MigratorCest.php)
- the SQLite integration tests in [orm/tests/SQLite](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/tests/SQLite)

## Summary

MySQL is still the dialect the ORM feels most shaped around, but not yet the dialect it is most rigorously proving.

The biggest gaps are:

- connection hardening is too thin
- `save()` and `upsert()` still have correctness issues
- identity handling assumes a simple integer `id` too often
- MySQL schema and migration paths are only lightly verified
- the end-to-end test suite gives SQLite more realistic coverage than MySQL

So the `0.9.0` ORM milestone should not start with PostgreSQL features. It should start by making the MySQL path correct, boring, and heavily tested.

## What Already Feels Good

### Prepared statements are now the default path in the SQL executor

[SQLQuery.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLQuery.php) is already doing the right broad thing:

- values go through `prepare(...)`
- parameters are normalized for enums, dates, booleans, arrays, and objects
- the execution layer is no longer string-concatenating normal value inputs

That gives us a decent foundation to build on.

### There is already a useful dialect seam

[SqlDialectHelper.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Util/SqlDialectHelper.php) and [SQLColumnDefinition.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLColumnDefinition.php) already show the right direction:

- identifier quoting varies by dialect
- SQLite and PostgreSQL branches exist in schema generation
- schema helpers are no longer completely MySQL-only

That means we do not need a total rewrite to move forward.

### Repository ergonomics are already close to the right public shape

[Repository.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Management/Repository.php) already offers the right basic verbs:

- `create(...)`
- `save(...)`
- `insert(...)`
- `update(...)`
- `softRemove(...)`
- `delete(...)`
- `restore(...)`

So the main work is correctness and behavior, not inventing a new mental model.

## High-Priority Findings For 0.9.0

### 1. `save()` has a real correctness bug in the existence check

File:

- [EntityManager.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Management/EntityManager.php)

Problem:

`save()` checks this:

```php
else if ($this->findBy(...)) {
  // update
}
```

But `findBy(...)` returns a `FindResult` object, and objects are always truthy in PHP. That means if an entity has a non-empty primary key, `save()` always goes down the update path whether the row actually exists or not.

Why it matters:

- this can silently turn an intended insert-or-fail decision into an update attempt
- behavior becomes harder to trust in service-layer code
- it makes `save()` less safe as the default creation/update method

Target for `0.9.0`:

- change this to a real existence check based on result state, for example `isOk()` plus `getTotal() > 0`
- add dedicated tests for:
  - existing row with id
  - missing row with id
  - object without id

### 2. Identity handling assumes a simple integer `id` too often

Files:

- [EntityManager.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Management/EntityManager.php)
- [SQLQuery.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLQuery.php)

Problems:

- insert follow-up queries fetch by hard-coded `id`
- `lastInsertId()` is treated like the universal source of truth
- results often assume the primary key is auto-increment and numeric

Why it matters:

- MySQL can support UUIDs and custom primary keys just fine, but the current ORM path keeps nudging everything back toward `id`
- even for MySQL, this makes repository behavior less general than the public API suggests

Target for `0.9.0`:

- make insert/save paths honor the resolved primary key field and column
- avoid assuming `id` when `primaryKeyField` is configured
- keep MySQL auto-increment happy, but stop making it the only correct story

### 3. `upsert()` is still MySQL-first and not solid enough yet

Files:

- [EntityManager.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Management/EntityManager.php)
- [DuplicateKeyUpdatableTrait.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Traits/DuplicateKeyUpdatableTrait.php)

Problems:

- `upsert()` still carries a TODO
- the main path is `ON DUPLICATE KEY UPDATE`, which is fine for MySQL, but the result semantics are not stable enough yet
- it returns `InsertResult` even for update-shaped outcomes
- it still leans on `lastInsertId()` in situations where that is not the right truth source

Why it matters:

- MySQL should be the dialect where `upsert()` is strongest, not merely present
- if MySQL upsert is ambiguous today, later dialect parity work will become much harder

Target for `0.9.0`:

- make MySQL `upsert()` a first-class, intentionally tested path
- define clear result behavior for:
  - inserted row
  - updated existing row
  - bulk upsert
- document that MySQL uses `ON DUPLICATE KEY UPDATE`, while other dialects use their own native form later

### 4. MySQL connection setup is too thin for a “reference” dialect

Files:

- [MySQLDataSource.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/MySQLDataSource.php)
- [DataSource.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/DataSource.php)
- [DBFactory.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/DBFactory.php)

Problems:

- DSNs do not include charset
- there is no single consistent place where MySQL PDO options are set
- `MySQLDataSource` and the generic `DataSource`/`DBFactory` path duplicate behavior
- `connect()` / `disconnect()` in the specialized data source classes are effectively placeholders

Why it matters:

- charset bugs are subtle and painful
- inconsistent connection configuration makes bugs look random
- connection behavior should not depend on whether the app went through `MySQLDataSource` or generic `DataSource`

Target for `0.9.0`:

- unify MySQL connection creation behind one trusted path
- include charset in the DSN or connection init consistently
- explicitly set the PDO options we want to rely on
- decide whether the specialized `MySQLDataSource` class still needs to exist once the generic path is good

### 5. MySQL schema generation is usable, but not yet configurable or deeply proven

Files:

- [Schema.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/Schema.php)
- [SQLColumnDefinition.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLColumnDefinition.php)

Problems:

- MySQL table creation hard-codes:
  - `ENGINE=InnoDB`
  - `DEFAULT CHARSET=utf8mb4`
  - `COLLATE=utf8mb4_general_ci`
- this is a sensible default, but it is still embedded directly in schema generation
- schema tests are mostly unit-style and not broad enough to catch real-world MySQL differences

Why it matters:

- we should keep the current defaults, but make the behavior more intentional
- if MySQL is the reference dialect, schema behavior should be test-backed and configurable where it matters

Target for `0.9.0`:

- keep the current defaults, but route them through a clearer MySQL schema policy/config path
- add tests around generated DDL and schema introspection for MySQL specifically

## Medium-Priority Findings

### 6. Database and migration verification is present, but not trustworthy enough yet

Files:

- [DatabaseManagerCest.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/tests/Unit/DatabaseManagerCest.php)
- [MigratorCest.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/tests/Unit/MigratorCest.php)

Observations:

- `DatabaseManagerCest` is marked `#[Skip]`
- `MigratorCest` still contains skipped scenarios and hand-written MySQL setup SQL
- the suite is not giving MySQL the same realistic confidence that SQLite now gets

Target for `0.9.0`:

- unskip or replace the MySQL database-manager tests
- strengthen migration coverage around:
  - migrations table setup
  - `runAll`
  - `revertAll`
  - `redo`
- avoid depending on fragile manual bootstrap where the ORM should own the path

### 7. Error reporting still hides too much detail at the wrong layer

Files:

- [DataSource.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/DataSource.php)
- [DBFactory.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/DBFactory.php)

Problem:

several connection failures collapse into generic `DataSourceConnectionException` flows without preserving enough driver context.

Why it matters:

- MySQL support is hardest to stabilize when every connection failure looks the same
- we need better developer-facing signals while still keeping production output clean

Target for `0.9.0`:

- keep safe public exceptions
- preserve driver details in debug/test/dev paths
- make connection/configuration bugs faster to diagnose

## Verification Gap

This may be the biggest structural issue in the package right now:

- there are dedicated integration-style tests under [orm/tests/SQLite](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/tests/SQLite)
- MySQL mostly appears in older unit-style setup/schema tests
- the unit suite itself is wired to MySQL in [Unit.suite.yml](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/tests/Unit.suite.yml), but that is not the same thing as having strong flow coverage

So even when MySQL is the intended primary SQL dialect, SQLite currently has the more realistic day-to-day regression protection.

That should be reversed by the end of the MySQL phase of `0.9.0`.

## Recommended 0.9.0 Work Order

### Phase 1. Fix correctness bugs in the main repository path

Do first:

1. fix `save()` existence detection
2. fix primary-key handling in insert/save flows
3. harden MySQL `upsert()` semantics

Why first:

- these affect application correctness directly
- they influence how people write services every day
- there is no value in polishing migrations first if `save()` is still misleading

### Phase 2. Harden MySQL connections and defaults

Do next:

1. unify MySQL connection creation
2. add charset and connection option handling
3. reduce duplicated connection logic across `MySQLDataSource`, `DataSource`, and `DBFactory`

Why next:

- this lowers configuration surprises
- it also makes later debugging much easier

### Phase 3. Strengthen MySQL schema and migration behavior

Do next:

1. review MySQL DDL defaults and make them intentional
2. harden schema diff/alter flows
3. strengthen migration verification

Why next:

- schema and migration stability is part of what users mean by “ORM stability”

### Phase 4. Build real MySQL integration coverage

Do before calling the milestone done:

1. add MySQL flow tests similar in spirit to the SQLite flow tests
2. cover:
   - create
   - save
   - update
   - softRemove
   - restore
   - relations
   - upsert
3. unskip or replace old MySQL-only tests that are too fragile

Why last:

- some of these tests will be easier to write after the correctness fixes land
- but they must exist before the milestone is called complete

## What Should Wait Until After The MySQL Pass

These are important, but they should not block the first MySQL hardening slice:

- proper SQLite parity work
- full PostgreSQL parity
- a fully context-aware dialect-specific query builder API

Those are already tracked separately in:

- [postgresql-parity-and-dialect-query-builder-plan.md](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/docs/postgresql-parity-and-dialect-query-builder-plan.md)

## Definition Of Done For The MySQL Phase Of 0.9.0

We should consider the MySQL phase complete when:

1. `save()` and `upsert()` behave correctly and predictably.
2. MySQL connection handling is unified and explicit about charset/options.
3. MySQL schema and migration paths are stable enough to trust.
4. MySQL has realistic integration coverage, not just light unit coverage.
5. We can honestly say:
   - MySQL is the best-supported SQL dialect in Assegai ORM
   - SQLite and PostgreSQL parity work is building on a stable reference implementation, not a shaky one

## Recommended Next Step

Start implementation with the repository correctness pass:

1. fix `EntityManager::save()`
2. define the correct MySQL `upsert()` behavior
3. add focused regression tests for those two paths before touching broader schema work
