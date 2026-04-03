# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.8.0] - 2026-04-03

### Breaking Changes

- Removed hard dependency on `assegaiphp/core` — now optional (suggested). Add it explicitly if your project requires it.
- Added `psr/log` as a required dependency.

### Added

- AssegaiORM can now be used as a **standalone ORM** without the AssegaiPHP framework.
- New `OrmModule` class for framework-aware module integration.
- New `RepositoryParameterResolver` for dependency-injection aware repository resolution.
- New `OrmPackageInstaller` Composer installer for Assegai CLI discovery.
- New `OrmRuntime` support class.
- New CLI commands for **database management**: `DatabaseConfigure`, `DatabaseLoad`, `DatabaseSeed`, `DatabaseSetup`.
- New CLI commands for **migrations**: `MigrationCreate`, `MigrationDown`, `MigrationList`, `MigrationRedo`, `MigrationRefresh`, `MigrationSetup`, `MigrationUp`.
- GitHub Actions CI workflow (`.github/workflows/php.yml`) running tests on PHP 8.3 and latest PHP, including unit and SQLite integration test lanes.
- Separate PHPUnit configuration files: `phpunit.xml` (unit) and `phpunit.mysql.xml` (MySQL integration).
- New PHPUnit test suites: unit tests, MySQL integration tests, and SQLite integration tests.
- `SchemaAlterCest` for SQLite ALTER TABLE testing.
- `suggest` section in `composer.json` for optional AssegaiPHP packages.
- `extra` section in `composer.json` for Assegai CLI command and installer discovery.

### Changed

- Refactored all options classes for consistency: `FindManyOptions`, `FindOneOptions`, `FindOptions`, `FindRelationsOptions`, `FindWhereOptions`, `InsertOptions`, `JoinOptions`, `OrderByCondition`, `RemoveOptions`, `SaveOptions`, `UpdateOptions`, `UpsertOptions`.
- Improved internals of `DataSource`, `MySQLDataSource`, `PostgreSQLDataSource`, `RedisDataSource`, `SQLiteDataSource`, `EntityManager`, `DatabaseManager`, `Schema`, `SQLQuery`, `Migrator`, and `MigrationsList`.
- Updated `README.md` with badges, standalone usage guide, and expanded installation instructions for both standalone and AssegaiPHP projects.
- Updated package description in `composer.json` to reflect standalone-first positioning.
- Added documentation: `standalone-orm-roadmap.md`, `postgresql-parity-and-dialect-query-builder-plan.md`, `mysql-audit-for-0.9.0.md`.

**Full Changelog**: https://github.com/assegaiphp/orm/compare/0.7.9...0.8.0

## [0.7.9] - 2026-03-26

### Changed

- Refactor error retrieval methods for clarity and deprecate old ones.

**Full Changelog**: https://github.com/assegaiphp/orm/compare/0.7.8...0.7.9

## [0.7.8] - 2026-03-20

### Added

- Enhance SQL query handling with `SqlIdentifier` and improved binding.

**Full Changelog**: https://github.com/assegaiphp/orm/compare/0.7.7...0.7.8

## [0.7.7] - 2026-03-19

### Changed

- Enhance relation handling and update README with new sections.

**Full Changelog**: https://github.com/assegaiphp/orm/compare/0.7.6...0.7.7

## [0.7.6] - 2026-03-15

### Added

- Improve support for SQLite databases and refactor how database connections are managed.
- Enhance ENUM handling and improve relation normalization.

**Full Changelog**: https://github.com/assegaiphp/orm/compare/0.7.5...0.7.6

## [0.7.5] - 2026-01-14

### Fixed

- Fix issue with ManyToMany relations.

**Full Changelog**: https://github.com/assegaiphp/orm/compare/0.7.4...0.7.5

## [0.7.4] - 2026-01-03

### Changed

- Refactored `SQLColumnDefinition` to use `ColumnType` for the `$type` parameter, improving type safety and code clarity.

**Full Changelog**: https://github.com/assegaiphp/orm/compare/0.7.3...0.7.4

## [0.7.3] - 2026-01-03

### Changed

- Enhance `softRemove` method to accept a customizable primary key field.

**Full Changelog**: https://github.com/assegaiphp/orm/compare/0.7.2...0.7.3

## [0.7.2] - 2026-01-02

**Full Changelog**: https://github.com/assegaiphp/orm/compare/0.7.1...0.7.2

## [0.7.1] - 2026-01-02

### Changed

- Update `assegaiphp/core` and other dependencies to latest versions.

**Full Changelog**: https://github.com/assegaiphp/orm/compare/0.7.0...0.7.1

## [0.7.0] - 2025-10-26

### Changed

- Rename `GeneralConverters` to `BasicTypeConverter`.

**Full Changelog**: https://github.com/assegaiphp/orm/compare/0.6.32...0.7.0

[0.8.0]: https://github.com/assegaiphp/orm/compare/0.7.9...0.8.0
[0.7.9]: https://github.com/assegaiphp/orm/compare/0.7.8...0.7.9
[0.7.8]: https://github.com/assegaiphp/orm/compare/0.7.7...0.7.8
[0.7.7]: https://github.com/assegaiphp/orm/compare/0.7.6...0.7.7
[0.7.6]: https://github.com/assegaiphp/orm/compare/0.7.5...0.7.6
[0.7.5]: https://github.com/assegaiphp/orm/compare/0.7.4...0.7.5
[0.7.4]: https://github.com/assegaiphp/orm/compare/0.7.3...0.7.4
[0.7.3]: https://github.com/assegaiphp/orm/compare/0.7.2...0.7.3
[0.7.2]: https://github.com/assegaiphp/orm/compare/0.7.1...0.7.2
[0.7.1]: https://github.com/assegaiphp/orm/compare/0.7.0...0.7.1
[0.7.0]: https://github.com/assegaiphp/orm/compare/0.6.32...0.7.0
