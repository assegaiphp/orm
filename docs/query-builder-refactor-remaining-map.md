# Query Builder Refactor Remaining Map

## Goal

Finish the `0.9.0` query-builder refactor so the SQL-family builders feel intentionally dialect-aware instead of MySQL-shaped with PostgreSQL and SQLite branches layered on top.

This map assumes:

- `mysql`, `pgsql`, and `sqlite` must feel stable by the end of `0.9.0`
- `mariadb` should remain aligned with the MySQL family
- `mssql` is the next SQL-family backend after the core three are stable
- the broader framework should continue speaking in terms of `data_source`, not just `database`

## Current Position

The top-level builder split is mostly in place.

The following root builders now exist and are used by the ORM:

- [MySQLQuery.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/MySql/MySQLQuery.php)
- [PostgreSQLQuery.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/PostgreSql/PostgreSQLQuery.php)
- [SQLiteQuery.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/SQLite/SQLiteQuery.php)
- [MariaDbQuery.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/MariaDb/MariaDbQuery.php)

The typed root surface already covers:

- `alter()`
- `create()`
- `drop()`
- `rename()`
- `describe()`
- `insertInto()`
- `select()`
- `update()`
- `deleteFrom()`
- `truncateTable()`
- `use()` only where it actually belongs

That is the right architectural direction.

What remains is the deeper builder and expression chain. A lot of the real mechanics still live in [src/Queries/Sql](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql), which means the public root is more honest now, but the internals are still more shared than they should be.

## Definition Of Done For This Refactor

We should treat the `0.9.0` query-builder refactor as complete when:

1. the root builders expose only features that are valid for that dialect
2. the main fluent chains beneath those roots are no longer pretending to be universally shared when they are not
3. the shared `Sql` namespace contains only genuinely cross-SQL-family primitives
4. MySQL, PostgreSQL, and SQLite all have passing integration coverage for the query paths users are likely to touch first
5. the resulting structure is clean enough that MSSQL can be added as a sibling instead of forcing edits into stable MySQL/PostgreSQL/SQLite code

## What Is Already Good Enough

These areas are in a reasonable place for `0.9.0`:

- dialect root switching in [SQLQuery.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLQuery.php)
- typed root builders for create/drop/alter/select/update/delete/rename
- PostgreSQL write-path parity around `RETURNING` and `ON CONFLICT`
- integration coverage for MySQL, PostgreSQL, and SQLite
- removal of obviously fake-universal methods such as `use()` from dialects that do not support them

These do not need another full rewrite before `0.9.0` ships.

## Remaining Work

### Phase 1. Finish the select-expression split

Files that still matter most:

- [SQLSelectExpression.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLSelectExpression.php)
- [SQLWhereClause.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLWhereClause.php)
- [SQLHavingClause.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLHavingClause.php)
- [SQLLimitClause.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLLimitClause.php)

Goal:

- keep portable select fluency shared only where it truly belongs
- move PostgreSQL-only select behavior into PostgreSQL-specific builders
- move MySQL-family select behavior into MySQL/MariaDB builders
- make the follow-on chain after `select()` feel as typed as the root call itself

This is the single most important remaining refactor slice.

### Phase 2. Split join and table-reference behavior where needed

Files to audit:

- [SQLJoinExpression.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLJoinExpression.php)
- [SQLJoinSpecification.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLJoinSpecification.php)
- [SQLTableReference.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLTableReference.php)

Goal:

- keep ordinary inner/left/right join shape portable where possible
- isolate any dialect-specific join semantics or quoting assumptions
- avoid MySQL-flavored table-reference assumptions leaking into future MSSQL support

This phase is about preventing deeper SQL composition from staying accidentally MySQL-first.

### Phase 3. Finish assignment and condition-chain cleanup

Files to audit:

- [SQLAssignmentList.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLAssignmentList.php)
- [SQLWhereClause.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLWhereClause.php)
- [SQLDeleteFromStatement.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLDeleteFromStatement.php)
- [SQLUpdateDefinition.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLUpdateDefinition.php)

Goal:

- keep condition building portable where it truly is portable
- keep assignment targets and `SET` rendering correct across all supported SQL dialects
- expose PostgreSQL-only write extensions only on PostgreSQL-specific builders
- preserve SQLite and MySQL stability while removing shared assumptions that do not actually generalize

This is where correctness bugs are most likely to hide.

### Phase 4. Reclassify shared SQL primitives

Files to review as a group:

- [SQLCreateTableStatement.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLCreateTableStatement.php)
- [SQLDropTableStatement.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLDropTableStatement.php)
- [SQLRenameTableStatement.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLRenameTableStatement.php)
- [SQLColumnDefinition.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLColumnDefinition.php)
- [SQLPrimaryGeneratedColumn.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Queries/Sql/SQLPrimaryGeneratedColumn.php)

Goal:

- keep only truly shared SQL-family primitives in `Sql/`
- move anything that is really MySQL-family-only or PostgreSQL-specific into the dialect namespaces
- make `Sql/` look like a stable family core rather than a dumping ground

This phase matters most for MSSQL readiness.

### Phase 5. MSSQL readiness audit

Do not implement MSSQL in `0.9.0` unless it becomes necessary, but do leave the codebase ready for it.

That means checking that:

- shared builders do not assume MySQL-only syntax by default
- capability-specific methods only exist where they belong
- `create`, `drop`, `alter`, `select`, `update`, and `delete` can gain an MSSQL sibling without reopening already-stable builders

The output of this phase should be a small punch list, not a new feature track.

## What Should Not Be Forced Into `0.9.0`

These are useful, but not required to call the refactor stable:

- MSSQL implementation itself
- non-SQL backends like MongoDB, Neo4j, or Elasticsearch
- a universal abstraction that hides every backend-specific idea
- a perfect renderer split for every SQL statement if the current typed-builder approach is already readable and stable

`0.9.0` needs a stable SQL-family architecture, not a finished multi-backend universe.

## Recommended Execution Order

1. finish the select-expression chain
2. clean up joins and table references
3. finish assignment and condition-chain cleanup
4. reclassify shared SQL primitives
5. run the MySQL/PostgreSQL/SQLite matrix after each slice
6. write a short MSSQL-readiness punch list at the end

## Verification Expectations

Every slice should keep these green:

- `composer validate --strict`
- `composer test`
- `composer test:mysql`
- `composer test:sqlite`
- `composer test:pgsql`

Where possible, add focused unit coverage in:

- [SqlDialectRenderingTest.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/tests/PHPUnit/Unit/SqlDialectRenderingTest.php)

and integration coverage in the existing MySQL/PostgreSQL/SQLite lanes.

## Practical Read On Remaining Scope

The refactor is past the halfway point.

A fair estimate is:

- roughly `60-70%` complete overall
- the top-level root-builder split is mostly done
- the remaining `30-40%` is concentrated in the deeper fluent chain and shared SQL primitives

That remaining portion is smaller in file count than the first half, but it is the part that determines whether the architecture really stays open for MSSQL and later SQL-family additions.
