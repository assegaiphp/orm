# Entity-Driven Database Sync Plan

## Goal

Add a new entity-first workflow to Assegai:

- `assegai database:sync`
- `assegai db:sync`

The command should treat entity files as the source of truth, inspect the entities in scope, compare them to the live database schema, and generate normal migration files from the diff.

This does **not** replace the current manual migration workflow. It adds a preferred higher-level workflow on top of it.

## Why This Work Matters

Right now the framework can:

- define schema intent in entity attributes
- inspect entities through [EntityInspector.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Management/Inspectors/EntityInspector.php)
- inspect and alter tables through [Schema.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/Schema.php)
- run SQL-file migrations through the console migrators

But it still asks developers to manually keep three things in sync:

1. entity files
2. live database schema
3. handwritten migration files

That is where drift creeps in.

The new sync command should reduce that drift by making entity metadata the canonical schema input, while still preserving explicit migration history as an output artifact.

## External Reference Points

Two useful reference ideas:

- Diesel CLI keeps migrations as first-class artifacts and can regenerate schema representation after schema-affecting commands. That is a good model for "schema source and migration history work together" rather than competing with each other. Source: [Configuring Diesel CLI](https://diesel.rs/guides/configuring-diesel-cli/)
- Marko-style tutorial ergonomics are a useful reminder that developers want a simple, guided path from models to a working database, even if the internal architecture is more capable than the tutorial shows.

We should borrow the good parts without forcing Assegai into a Rust- or Laravel-shaped mental model.

## Product Direction

The preferred Assegai workflow going forward should be:

1. define or update entities
2. run `assegai db:sync`
3. review the generated migration
4. run the normal migration commands

The manual workflow remains supported:

1. run `assegai migration:create`
2. write SQL by hand
3. run the normal migration commands

The new workflow is additive, not breaking.

## Command UX

### Primary command

```bash
assegai database:sync
assegai db:sync
```

### Proposed options

```bash
assegai db:sync \
  --db-type=mysql \
  --db-name=orders_db
```

```bash
assegai db:sync \
  --db-type=sqlite \
  --db-name=marketace_db \
  --entity="App\\Users\\UserEntity" \
  --entity="App\\Orders\\OrderEntity"
```

### Recommended option surface

- `--db-type=mysql|sqlite|pgsql`
- `--db-name=<name>`
- `--entity=<fqcn>` repeatable
- `--path=<directory>` repeatable
- `--module=<module class>` optional future convenience
- `--data-source=<driver:name>` optional shorthand for the above two flags
- `--dry-run` show the plan without writing files
- `--name=<migration-name>` override the generated migration name
- `--write-empty` write a migration even when the diff is empty
- `--join-tables=auto|off`
- `--apply` optional later phase, not required for the first implementation

### Defaults

- if no entity scope is provided, scan the current workspace
- if no migration name is provided, generate one like `sync_schema`
- if no join-table mode is provided, use the project default

## Workspace Scanning Rules

This command should assume a **single project workspace**, not a monorepo.

Entity discovery should:

1. start from the current workspace's `composer.json`
2. resolve the workspace's own PSR-4 autoload roots
3. scan only that workspace unless the user explicitly points elsewhere
4. load only classes marked with [Entity.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Attributes/Entity.php)

Recommended scanning precedence:

1. explicit `--entity`
2. explicit `--path`
3. workspace PSR-4 scan

That keeps the feature predictable and avoids hidden assumptions about sibling repos.

## What The Command Should Generate

`database:sync` should generate a normal migration folder in the existing migrations layout, not bypass it.

Recommended output:

- `migrations/<driver>/<db>/<timestamp>_<name>/up.sql`
- `migrations/<driver>/<db>/<timestamp>_<name>/down.sql`
- `migrations/<driver>/<db>/<timestamp>_<name>/sync.json`

Why add `sync.json`:

- records which entities were scanned
- records the datasource used
- records whether join-table inference was enabled
- records warnings or manual follow-up items
- helps future tooling and upgrade diagnostics

## Proposed Architecture

### 1. Keep the command in `console`

The CLI UX belongs in `console`, likely alongside:

- [DatabaseConfigure.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/console/src/Commands/Database/DatabaseConfigure.php)
- [DatabaseSetup.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/console/src/Commands/Database/DatabaseSetup.php)

Proposed new command:

- `console/src/Commands/Database/DatabaseSync.php`

### 2. Put sync planning in `orm`

The entity-to-schema planning logic belongs in `orm`, not `console`.

Recommended new services:

- `EntityScopeResolver`
- `SchemaSnapshotReader`
- `EntitySchemaSnapshotBuilder`
- `JoinTableInferenceService`
- `SchemaDiffPlanner`
- `MigrationSqlGenerator`
- `SyncMigrationWriter`

`console` should orchestrate. `orm` should decide what the schema means.

### 3. Reuse existing metadata and schema machinery

The plan should build on:

- [EntityInspector.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Management/Inspectors/EntityInspector.php)
- [EntityMetadata.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Metadata/EntityMetadata.php)
- [RelationMetadata.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Metadata/RelationMetadata.php)
- [Schema.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/DataSource/Schema.php)
- [SchemaChangeManifest.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Migrations/SchemaChangeManifest.php)

But we should not call `Schema::alter(...)` directly from the command and call it done. That path is useful, but it is currently too table-local and too live-database-oriented to be the whole sync architecture.

Instead, the new planner should produce an intermediate diff model, and SQL generation should happen from that model.

## Sync pipeline

### Step 1. Resolve the datasource

Inputs:

- `--db-type`
- `--db-name`
- optional `--data-source`

Output:

- a resolved datasource identity such as `sqlite:blog_api_db`

This should align with the current Assegai direction around fully qualified datasource names.

### Step 2. Resolve entity scope

Inputs:

- explicit entities
- explicit paths
- or workspace scan

Output:

- a stable ordered list of entity FQCNs

Rules:

- skip entities with `#[Entity(..., synchronize: false)]`
- fail early on invalid classes
- warn when duplicate table names are detected

### Step 3. Build the desired schema snapshot

For every entity in scope, derive:

- table name
- columns
- primary key
- nullability
- defaults
- indexes
- unique constraints
- foreign keys
- relation-driven join columns
- relation-driven join tables when enabled

This desired snapshot is the entity-side truth model.

### Step 4. Read the live schema snapshot

For the target datasource, inspect the current database:

- tables
- columns
- indexes
- unique constraints
- foreign keys
- join tables already present

This snapshot should be dialect-aware and should reuse the existing schema readers where possible.

### Step 5. Compute a diff plan

Generate a normalized diff model, for example:

- create table
- drop table
- add column
- alter column
- drop column
- add index
- drop index
- add foreign key
- drop foreign key
- create join table
- drop join table

This diff model is the important seam. It keeps planning separate from SQL rendering.

### Step 6. Generate `up.sql` and `down.sql`

Render the diff to SQL for the active dialect.

Rules:

- `up.sql` applies the desired entity-driven changes
- `down.sql` reverses them using the pre-sync live schema snapshot
- when a reverse operation is ambiguous or lossy, generate the best safe reverse we can and emit a warning in `sync.json`

### Step 7. Write the migration artifact

Write the standard migration directory and print:

- entities scanned
- tables affected
- join tables inferred
- warnings
- next recommended command

## Join-table strategy

This needs to be smart, but not magical in a dangerous way.

### Goal

When many-to-many relationships are declared, the sync command should be able to create and configure join tables without developers having to hand-specify every detail.

### Proposed behavior

Add a sync-level setting for join-table inference:

- `auto`
- `off`

This plan assumes "opt-out" means the feature is enabled by default and can be turned off easily per project, per command run, or per relation.

Recommended product behavior:

- default project setting: `auto`
- easy opt-out via config or CLI

That matches the intention that the feature should help by default, but still be configurable.

### Inference rules

When join-table inference is `auto`:

1. only act on owner-side many-to-many relations
2. honor an explicit [JoinTable.php](/home/amasiye/development/atatusoft/projects/external/assegaiphp/orm/src/Attributes/Relations/JoinTable.php) when it is present
3. if `JoinTable(..., synchronize: false)` is set, skip the table
4. if no `JoinTable` is present, generate:
   - table name
   - join column
   - inverse join column
   - composite primary key or unique key
   - foreign keys
5. if the relation metadata is ambiguous, warn and require the developer to be explicit

### Naming defaults

Use deterministic names so repeated syncs remain stable.

Recommended defaults:

- join table: `<owner_table>_<inverse_table>`
- owner FK column: `<owner_table_singular>_id`
- inverse FK column: `<inverse_table_singular>_id`

If the framework already has a stronger naming strategy elsewhere, use that strategy centrally instead of duplicating it in the command.

## Safety rules

The first version should be conservative.

### Default behavior

- generate migration files
- do not auto-apply them
- show a readable change summary
- warn loudly about destructive operations

### Destructive changes

Examples:

- dropping tables
- dropping columns
- tightening nullability
- changing column types in incompatible ways

Recommended behavior:

- allow generation
- mark these steps as destructive in `sync.json`
- print a warning in the CLI summary
- consider a future `--allow-destructive` gate if we find teams want stricter defaults

### Rename detection

Rename detection is dangerous to guess.

Recommendation:

- first release should **not** guess renames automatically
- treat unknown renames as drop + add
- later we can add explicit rename hints or heuristics

That keeps the implementation honest.

## Coexistence with the manual workflow

We should keep both workflows clearly supported.

### Manual SQL workflow

- `migration:create`
- hand-edit `up.sql` and `down.sql`
- `migration:up`
- `migration:down`

### Entity-driven workflow

- edit entities
- `database:sync`
- review generated migration
- `migration:up`

The generated migration files should look normal and remain editable. Developers should never feel trapped in an opaque one-way system.

## Recommended configuration

Add a project-level config section for sync defaults, for example under ORM config:

```php
'orm' => [
  'sync' => [
    'join_tables' => 'auto',
    'write_empty' => false,
    'scan_paths' => ['src'],
    'migration_name' => 'sync_schema',
  ],
],
```

The exact config file location can be decided later, but the feature should not force every project to retype the same flags.

## Implementation phases

### Phase 1: scaffolding and dry-run plan

Deliver:

- `database:sync` / `db:sync` command
- datasource resolution
- workspace entity scan
- desired schema snapshot
- live schema snapshot
- diff summary output
- `--dry-run`

No SQL files yet. This phase proves discovery and planning.

### Phase 2: migration generation

Deliver:

- write `up.sql`
- write `down.sql`
- write `sync.json`
- support create/add/change/drop for tables and columns
- reuse existing migration folder layout

This is the first version that is useful for daily development.

### Phase 3: relation-aware sync

Deliver:

- foreign keys
- indexes from entity metadata
- relation-driven join columns
- many-to-many join-table inference
- configurable join-table opt-out

This is where the feature becomes truly entity-first.

### Phase 4: polish and dialect parity

Deliver:

- MySQL, SQLite, and PostgreSQL behavior aligned
- better diff explanations
- better destructive-change warnings
- optional `--apply`
- optional rename hints

## Testing strategy

This feature needs more than happy-path unit tests.

### Unit tests

Add fast tests for:

- entity scope resolution
- diff planning
- join-table inference
- SQL rendering per dialect
- destructive-change classification

### Integration tests

For each dialect:

- start with a live schema
- change entities
- run sync
- assert generated `up.sql`
- apply migration
- inspect resulting schema
- run `down.sql`
- inspect rollback result

SQLite should be the first fully automated integration lane because it is easiest to run in CI.

### Golden-file tests

This feature is a good candidate for snapshot-style tests:

- entities in
- migration files out

That will help keep CLI output and generated SQL stable.

## Risks

### 1. Down migrations are harder than up migrations

Generating `up.sql` is relatively straightforward. Generating reliable `down.sql` requires a good snapshot of the pre-sync schema and careful dialect handling.

### 2. Schema diffs can become dialect-specific quickly

SQLite alter behavior is not the same as MySQL or PostgreSQL. The diff model must stay portable, but the renderer must remain dialect-aware.

### 3. Relationship metadata may still need hardening

Many-to-many and join-column behavior is where ORM drift usually gets subtle. This feature will expose weak spots in relation metadata quickly.

### 4. Developers may expect "sync" to mutate the database immediately

We should be explicit in docs and command help:

- `database:sync` generates migrations
- migration commands apply them

If we later add `--apply`, that should remain an explicit opt-in.

## Definition of done

We should consider the feature ready when:

1. `assegai db:sync` can scan a workspace or explicit entity list
2. it can generate normal migration files from entity diffs
3. it supports MySQL, SQLite, and PostgreSQL
4. join-table inference works predictably and is configurable
5. manual migrations still work unchanged
6. docs clearly explain when to use sync vs manual SQL

## Recommended next implementation slice

Start with Phase 1 plus the migration writer shell from Phase 2:

1. add `DatabaseSync` command in `console`
2. add entity-scope resolution in `orm`
3. build a desired schema snapshot model from entity metadata
4. build a live schema snapshot reader
5. print a dry-run diff summary
6. once the diff model feels trustworthy, add SQL generation and migration writing

That gives us the safest path to a feature we can trust, instead of jumping straight to SQL file generation before the planner is stable.
