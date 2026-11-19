<?php

namespace Assegai\Orm\Interfaces;

use Assegai\Orm\DataSource\DataSource;
use Throwable;

/**
 * Interface for handling database migrations
 */
interface IMigration
{
  /**
   * Method to run when upgrading the database
   * @param DataSource $dataSource
   * @return void
   * @throws Throwable if application logic or a database operation fails.
   */
  public function up(DataSource $dataSource): void;

  /**
   * Method to run when downgrading the database
   * @param DataSource $dataSource
   * @return void
   * @throws Throwable if application logic or a database operation fails.
   */
  public function down(DataSource $dataSource): void;

  /**
   * Get the name of the migration.
   *
   * This method retrieves the name of the migration by extracting the file name of the current migration class file.
   *
   * @return string The name of the migration.
   */
  public function getName(): string;
}
