<div align="center">
    <a href="https://assegaiphp.com/" target="blank"><img src="https://assegaiphp.com/images/logos/logo-cropped.png" width="200" alt="Assegai Logo"></a>
</div>

<p align="center">A progressive PHP framework for building efficient and scalable server-side applications.</p>

## Description

An object-relational mapper for [AssegaiPHP](https://github.com/assegaiphp).

## Installation

```bash
$ composer require assegaiphp/orm
```

## Guide map

This package is designed to feel familiar to teams coming from TypeORM:

- entities describe persistence shape
- repositories are injected into services
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

In an Assegai application, you can also inject the repository and let the entity metadata select the SQLite
connection:

```php
<?php

namespace App\Notes;

use App\Entities\Note;
use Assegai\Core\Attributes\Injectable;
use Assegai\Orm\Attributes\InjectRepository;
use Assegai\Orm\Management\Repository;

#[Injectable]
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

## Support

Assegai is an MIT-licensed open source project. It can grow thanks to the sponsors and support by the amazing backers. If you'd like to join them, please [read more here](https://docs.assegaiphp.com/support).

## Stay in touch

* Author - [Andrew Masiye](https://twitter.com/feenix11)
* Website - [https://assegaiphp.com](https://assegaiphp.com/)
* Twitter - [@assegaiphp](https://twitter.com/assegaiphp)

## License

Assegai is [MIT licensed](LICENSE).
