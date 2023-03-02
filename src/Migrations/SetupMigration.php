<?php

namespace Assegai\Orm\Migrations;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\Schema;
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
    $createTableResult = Schema::createIfNotExists(SchemaMigrationsEntity::class);

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
    $dropTableResult = Schema::dropIfExists(SchemaMigrationsEntity::class);

    if (false === $dropTableResult)
    {
      throw new MigrationException("Failed to downgrade from " . $this->getName());
    }
  }
}