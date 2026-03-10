<?php

namespace Assegai\Orm\Migrations;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\Schema;
use Assegai\Orm\DataSource\SchemaOptions;
use Assegai\Orm\Exceptions\MigrationException;
use Assegai\Orm\Exceptions\ORMException;

/**
 * A migration to create the schema migrations table.
 */
class SetupMigration extends Migration
{
  /**
   * @inheritDoc
   * @param DataSource $dataSource
   * @throws MigrationException
   * @throws ORMException
   */
  public function up(DataSource $dataSource): void
  {
    $options = new SchemaOptions(
      dbName: $dataSource->getName(),
      dialect: $dataSource->getDialect(),
    );
    $createTableResult = Schema::createIfNotExists(SchemaMigrationsEntity::class, $options);

    if(false === $createTableResult)
    {
      throw new MigrationException("Failed to upgrade to " . $this->getName());
    }
  }

  /**
   * @inheritDoc
   * @param DataSource $dataSource
   * @throws MigrationException
   * @throws ORMException
   */
  public function down(DataSource $dataSource): void
  {
    $options = new SchemaOptions(
      dbName: $dataSource->getName(),
      dialect: $dataSource->getDialect(),
    );
    $dropTableResult = Schema::dropIfExists(SchemaMigrationsEntity::class, $options);

    if (false === $dropTableResult)
    {
      throw new MigrationException("Failed to downgrade from " . $this->getName());
    }
  }
}
