<?php

namespace Assegai\Orm\Migrations;

use Assegai\Orm\Attributes\Entity;
use Stringable;

/**
 * Class SchemaChangeManifest
 *
 * A readonly class representing a manifest of schema changes.
 *
 * @package Assegai\Orm\Migrations
 */
readonly class SchemaChangeManifest implements Stringable
{
  /**
   * @param Entity $entity The entity for which schema changes are being made.
   * @param array $addList An array of DDL statements to add new columns to the entity's schema.
   * @param array $updateList An array of DDL statements to update existing columns in the entity's schema.
   * @param array $dropList An array of DDL statements to drop columns from the entity's schema.
   */
  public function __construct(
    public Entity $entity,
    public array $addList = [],
    public array $updateList = [],
    public array $dropList = [],
  )
  {}

  /**
   * Returns a string representation of the schema change manifest.
   *
   * The returned string is a SQL statement that can be used to alter the
   * schema of the entity in a database.
   *
   * @return string The SQL statement to alter the schema of the entity.
   */
  public function __toString(): string
  {
    $tableName = $this->entity->table;
    $dataDefinitionStatement = "ALTER TABLE $tableName (";

    foreach ($this->addList as $addDDLStatement)
    {
      $dataDefinitionStatement .= "ADD $addDDLStatement, ";
    }

    foreach ($this->updateList as $changeDDLStatement)
    {
      $dataDefinitionStatement .= "CHANGE $changeDDLStatement, ";
    }

    foreach ($this->dropList as $dropDDLStatement)
    {
      $dataDefinitionStatement .= "DROP $dropDDLStatement, ";
    }

    return trim($dataDefinitionStatement, " \t\n\r\0\x0B,") . ")";
  }
}