# Standalone ORM Roadmap

## Goal

Keep `assegaiphp/orm` usable as a standalone package in any PHP project while preserving smooth integration inside
Assegai applications.

## What has already changed

- runtime config and logging now flow through `Assegai\\Orm\\Support\\OrmRuntime`
- datasource resolution no longer directly imports `Assegai\\Core\\Config`
- repository selection no longer directly imports `ModuleManager`
- the package no longer needs `assegaiphp/core` in its runtime dependencies
- `core` no longer hardcodes `#[InjectRepository]`
- `core` now exposes a package resolver registry and a module-level injector configuration seam
- `Assegai\\Orm\\Assegai\\OrmModule` now registers repository resolution through that seam
- ORM CLI commands are now exposed through package metadata instead of being statically registered by the base CLI

## What still needs to happen

### 1. Keep framework behavior opt-in

- Assegai-specific convenience should keep working when `assegaiphp/core` is installed
- standalone projects should never need the framework package just to create a datasource or repository
- the repository attribute and `OrmModule` should stay in the optional bridge layer, not the standalone core runtime

### 2. Move integration docs into a clear optional section

- standalone usage should be the default story in package docs
- Assegai integration should be documented as an add-on, not the main identity of the package

### 3. Split bridge concerns later if needed

If the Assegai integration grows beyond lightweight convenience, move it into a separate bridge package such as:

- `assegaiphp/orm-assegai`

That would let `assegaiphp/orm` stay focused on:

- entities
- repositories
- datasources
- schema
- migrations
- query building

## Success criteria

We can call the decoupling complete when:

1. `assegaiphp/orm` installs without `assegaiphp/core`
2. standalone examples in the README work with only ORM dependencies
3. the fast PHPUnit lane and SQLite suite stay green
4. Assegai projects still get the optional repository/config conveniences they expect through `OrmModule`
5. `assegai add orm` is the one-step workflow for installing and wiring ORM support in framework projects
