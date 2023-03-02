<?php

namespace Assegai\Orm\Interfaces;

use Assegai\Orm\DataSource\DataSource;

/**
 * Interface for handling database migrations
 */
interface IMigration
{
  /**
   * Method to run when upgrading the database
   * @param DataSource $dataSource
   * @return void
   */
  public function up(DataSource $dataSource): void;

  /**
   * Method to run when downgrading the database
   * @param DataSource $dataSource
   * @return void
   */
  public function down(DataSource $dataSource): void;
}