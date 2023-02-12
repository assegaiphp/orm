<?php

namespace Assegai\Orm\Migrations;

use Assegai\Orm\Interfaces\IMigration;

/**
 * Abstract Migration class that serves as a base for all database migrations
 */
abstract class Migration implements IMigration
{
  /**
   * Abstract method that should be implemented to specify database migrations to run when upgrading the database
   *
   * @return void
   */
  public abstract function up(): void;

  /**
   * Abstract method that should be implemented to specify database migrations to run when downgrading the database
   *
   * @return void
   */
  public abstract function down(): void;
}