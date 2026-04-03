<div align="center">
    <a href="https://assegaiphp.com/" target="blank"><img src="https://assegaiphp.com/images/logos/logo-cropped.png" width="200" alt="Assegai Logo"></a>
</div>

<p align="center">
  <a href="https://github.com/assegaiphp/orm/releases"><img alt="Latest release" src="https://img.shields.io/github/v/release/assegaiphp/orm?display_name=tag&sort=semver&style=flat-square"></a>
  <a href="https://github.com/assegaiphp/orm/actions/workflows/php.yml"><img alt="Tests" src="https://img.shields.io/github/actions/workflow/status/assegaiphp/orm/php.yml?branch=main&label=tests&style=flat-square"></a>
  <img alt="PHP 8.3+" src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white">
  <a href="https://github.com/assegaiphp/orm/blob/main/LICENSE"><img alt="License" src="https://img.shields.io/github/license/assegaiphp/orm?style=flat-square"></a>
  <img alt="Status 0.9.0 rewrite in progress" src="https://img.shields.io/badge/status-0.9.0%20rewrite%20in%20progress-f59e0b?style=flat-square">
</p>

<p align="center">A standalone ORM for modern PHP applications, with optional AssegaiPHP integration.</p>

## Description

An object-relational mapper for modern PHP applications. You can use it on its own, or plug it into
[AssegaiPHP](https://github.com/assegaiphp) when you want repository injection and framework conventions.

## Installation

```bash
$ assegai add orm
```

That is the preferred path inside an Assegai workspace. It will:

- require `assegaiphp/orm` if it is missing
- import `OrmModule` into the root module
- make the ORM CLI commands available through package discovery

If you install the package manually first, `assegai add orm` is still safe to run afterward. It will just finish the
workspace wiring.

For standalone PHP projects that are not using Assegai, install the package directly:

```bash
$ composer require assegaiphp/orm
```

## Guide map

This package is designed to feel familiar to teams coming from TypeORM:

- entities describe persistence shape
- repositories can be used directly or injected into services in Assegai
- data sources decide where a feature reads and writes
- relations are explicit and ownership matters
- migrations evolve the schema deliberately

In the main Assegai guide set, the ORM track is:

- `core/docs/data-and-orm.md`
- `core/docs/orm-setup-and-data-sources.md`
- `core/docs/orm-entities-repositories-and-results.md`
- `core/docs/orm-relations.md`
- `core/docs/orm-migrations-and-database-workflows.md`

## Quick Start

[Overview & Tutorial](https://assegaiphp.com/guide/fundamentals/orm)

## Using it without Assegai

You can use AssegaiORM directly in any PHP project. The standalone path is:

1. configure named databases for the ORM runtime
2. create a `DataSource`
3. create or fetch repositories from that data source

```php
<?php

use App\Entities\Note;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Support\OrmRuntime;

OrmRuntime::configure([
  'databases' => [
    'sqlite' => [
      'app' => [
        'path' => __DIR__ . '/storage/app.sqlite',
      ],
    ],
  ],
]);

$dataSource = new DataSource(new DataSourceOptions(
  name: 'app',
  type: DataSourceType::SQLITE,
  database: 'app',
));

$notes = $dataSource->getRepository(Note::class);

$note = $notes->create((object)[
  'title' => 'First note',
  'body' => 'Stored without a framework',
]);

$created = $notes->save($note);
$allNotes = $notes->find()->getData();
```

## Using SQLite

SQLite is a good fit for local development, small apps, prototypes, and CLI tools. This ORM supports SQLite through
PDO, so the first step is to register a named SQLite connection in your app config.

Make sure the `pdo_sqlite` extension is enabled and that the folder for your database file already exists. The
configured `path` should be relative to your project's working directory.

```php
<?php

return [
  'databases' => [
    'sqlite' => [
      'app' => [
        'path' => 'storage/database/app.sqlite',
      ],
    ],
  ],
];
```

You can then point an entity at that SQLite data source:

```php
<?php

namespace App\Entities;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Queries\Sql\ColumnType;

#[Entity(
  table: 'notes',
  database: 'app',
  driver: DataSourceType::SQLITE,
)]
class Note
{
  #[PrimaryGeneratedColumn]
  public ?int $id = null;

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $title = '';

  #[Column(type: ColumnType::TEXT, nullable: true)]
  public ?string $body = null;
}
```

## Relation mental model

Relations follow the same ownership ideas you would expect from TypeORM:

- `OneToOne`: the owner side has `#[JoinColumn(...)]`
- `ManyToOne` and `OneToMany`: the foreign key lives on the `ManyToOne` side
- `ManyToMany`: the owner side has `#[JoinTable(...)]`

Load relations explicitly in `find()` and `findOne()` calls, and prefer writing through the owner side of the relation.

If you want to use SQLite directly through the ORM, create a `DataSource`, ensure the table exists, and then work with
the repository:

```php
<?php

use App\Entities\Note;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\Enumerations\DataSourceType;

$dataSource = new DataSource(new DataSourceOptions(
  entities: [],
  name: 'app',
  type: DataSourceType::SQLITE,
));

$dataSource->manager->query(<<<SQL
CREATE TABLE IF NOT EXISTS `notes` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `title` TEXT NOT NULL,
  `body` TEXT
)
SQL);

$notes = $dataSource->getRepository(Note::class);

$newNote = $notes->create([
  'title' => 'First note',
  'body' => 'Stored in SQLite',
]);

$notes->insert($newNote);

$allNotes = $notes->find()->getData();
$firstNote = $notes->findOne(['id' => 1])->getFirst();
```

## Using it inside Assegai

Inside Assegai, import `OrmModule` once or let `assegai add orm` wire it for you. That module registers the repository
resolver so `#[InjectRepository(...)]` can participate in the framework injector cleanly.

Once the module is present, you can inject the repository and let the entity metadata select the SQLite connection:

```php
<?php

namespace App\Notes;

use App\Entities\Note;
use Assegai\Orm\Attributes\InjectRepository;
use Assegai\Orm\Management\Repository;

class NotesService
{
  public function __construct(
    #[InjectRepository(Note::class)]
    private readonly Repository $notes,
  ) {
  }

  public function all(): array
  {
    return $this->notes->find()->getData();
  }
}
```

## Standalone first, framework optional

The ORM no longer needs `assegaiphp/core` to function. When the core package is present, the ORM can still read
framework config and repository metadata automatically. When it is not present, `Assegai\\Orm\\Support\\OrmRuntime`
acts as the lightweight runtime seam for config, module options, and logging.

## Support

Assegai is an MIT-licensed open source project. It can grow thanks to the sponsors and support by the amazing backers. If you'd like to join them, please [read more here](https://docs.assegaiphp.com/support).

## Stay in touch

* Author - [Andrew Masiye](https://twitter.com/feenix11)
* Website - [https://assegaiphp.com](https://assegaiphp.com/)
* Twitter - [@assegaiphp](https://twitter.com/assegaiphp)

## License

Assegai is [MIT licensed](LICENSE).
