<?php

namespace Assegai\Orm\Interfaces;

/**
 * Interface for handling database migrations
 */
interface IMigration
{
  /**
   * Method to run when upgrading the database
   * @return void
   */
  public function up(): void;

  /**
   * Method to run when downgrading the database
   * @return void
   */
  public function down(): void;
}