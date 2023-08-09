<?php

namespace Assegai\Orm\Migrations;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Interfaces\IMigration;

/**
 * Abstract Migration class that serves as a base for all database migrations
 */
abstract class Migration implements IMigration
{
  /**
   * Abstract method that should be implemented to specify database migrations to run when upgrading the database
   *
   * @param DataSource $dataSource The DataSource to be used for migrations.
   * @return void
   */
  public abstract function up(DataSource $dataSource): void;

  /**
   * Abstract method that should be implemented to specify database migrations to run when downgrading the database
   *
   * @param DataSource $dataSource The DataSource to be used for migrations.
   * @return void
   */
  public abstract function down(DataSource $dataSource): void;

  /**
   * Get the name of the migration.
   *
   * This method retrieves the name of the migration by extracting the file name of the current migration class file.
   *
   * @return string The name of the migration.
   */
  public function getName(): string
  {
    return get_called_class();
  }
}