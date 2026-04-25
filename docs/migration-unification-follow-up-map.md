# Migration Unification Follow-Up Map

## Goal

Capture the migration split clearly so we can unify it later without confusing it with the `0.9.0` ORM stability push.

This map intentionally treats migration unification as a separate track from the query-builder refactor.

## What The Repos Currently Do

Today the codebase carries two migration stories:

### 1. Standalone ORM migrations are class-based

Relevant files:

- [Migration.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Migrations/Migration.php)
- [Migrator.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Migrations/Migrator.php)

This path:

- generates PHP migration classes
- loads them from `database/migrations`
- executes `up()` and `down()` methods against a `DataSource`

### 2. Console migrations are SQL-file-based

Relevant files:

- [MigrationCreate.php](/home/amasiye/development/atatusoft/projects/internal/assegaiphp/console/src/Commands/Migration/MigrationCreate.php)
- [MySQLDatabaseMigrator.php](/home/amasiye/development/atatusoft/projects/internal/assegaiphp/console/src/Core/Migrations/MySQLDatabaseMigrator.php)
- [PostgreSQLDatabaseMigrator.php](/home/amasiye/development/atatusoft/projects/internal/assegaiphp/console/src/Core/Migrations/PostgreSQLDatabaseMigrator.php)
- [SQLiteDatabaseMigrator.php](/home/amasiye/development/atatusoft/projects/internal/assegaiphp/console/src/Core/Migrations/SQLiteDatabaseMigrator.php)

This path:

- creates migration directories under `migrations/<driver>/<db>/<timestamp>_<name>`
- writes `up.sql`
- writes `down.sql`
- applies those SQL files through driver-specific migrators

## Historical Read

The current repository history only shows that:

- the class-based ORM migration path appeared first in this iteration of the `orm` repo
- the SQL-file path arrived later in the current `console` repo

But the broader framework history matters more than this repo snapshot.

The real architectural intent, based on project context, is:

- the filesystem-first `up.sql` / `down.sql` strategy is the intended Assegai migration model
- the class-based strategy is a holdover that survived the port from an older CLI/framework generation
- the SQL-file strategy re-emerged once the drawbacks of the class-based path became clearer and external reference points like Diesel reinforced the value of file-based migration artifacts

So migration unification is not about choosing between two equally intended designs.
It is about finishing the cleanup of legacy drift.

## Recommendation

Treat the SQL-file strategy as the canonical long-term migration model.

That means the future steady state should be:

- CLI migrations use `up.sql` / `down.sql`
- standalone ORM migrations use the same artifact shape
- `database:sync` outputs the same migration artifact shape
- docs teach one migration story

## Why Not Force This Into `0.9.0`

`0.9.0` is already carrying:

- MySQL hardening
- SQLite cleanup
- PostgreSQL parity
- query-builder refactor work
- naming-strategy cleanup and other stability polish

Migration unification is important, but it is a different class of change:

- it cuts across CLI and ORM package boundaries
- it changes the documented migration story
- it may need a compatibility path for existing class-based standalone migrations
- it overlaps with the future `database:sync` work

That makes it a strong candidate for a follow-up milestone rather than something to squeeze into the end of `0.9.0` unless it becomes a blocking stability issue.

## Preferred Future Direction

### Phase 1. Declare the canonical migration artifact

Make one thing explicit:

- the canonical migration artifact is a directory containing `up.sql` and `down.sql`

Everything else should be evaluated relative to that decision.

### Phase 2. Decide what happens to class-based migrations

Options:

1. keep them as a legacy compatibility path only
2. provide a one-time conversion path to SQL-file migrations
3. remove them completely after a deprecation window

Recommended path:

- stop documenting class-based migrations as the primary workflow
- keep read compatibility for a transition period if needed
- prefer conversion or regeneration into SQL files rather than indefinite dual support

### Phase 3. Unify the standalone ORM migration API

The standalone ORM path should be able to:

- create SQL-file migrations
- run SQL-file migrations
- revert SQL-file migrations
- list ran and pending SQL-file migrations

That probably means either:

- rewriting [Migrator.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Migrations/Migrator.php) around SQL-file artifacts, or
- introducing a new SQL-file migrator in `orm` and treating the current class-based one as legacy

### Phase 4. Align docs and future sync output

Once the code is unified, then update:

- ORM docs
- website ORM guides
- console docs
- `database:sync` planning docs

The future sync flow should generate the same migration artifact the rest of the framework already expects.

## Questions To Resolve Later

When this work starts, we should answer these explicitly:

1. Do we keep class-based migration execution temporarily for backward compatibility?
2. Do we want a conversion command from class-based migrations to `up.sql` / `down.sql`, or is manual migration acceptable?
3. Should the standalone ORM migration directory layout move all the way to the CLI shape immediately?
4. Does migration unification happen before `database:sync`, or as part of the same larger migration-artifact cleanup?

## Suggested Milestone Placement

Recommended placement:

- do not treat this as required `0.9.0` work unless it becomes a blocker
- pick it up after the query-builder refactor and ORM stability work settle
- align it with the milestone where `database:sync` becomes active, because both features want the same migration artifact story

That sequencing avoids doing the same migration cleanup twice.

## Why This Map Exists

The purpose of this note is simple:

- do not forget that the split exists
- do not let the class-based migration ghost keep pretending it is a deliberate long-term dual strategy
- but also do not let migration unification derail the more urgent `0.9.0` ORM stability work
