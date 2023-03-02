<?php

namespace Assegai\Orm\Migrations;

use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Queries\DDL\DDLAddStatement;
use Assegai\Orm\Queries\DDL\DDLChangeStatement;
use Assegai\Orm\Queries\DDL\DDLDropStatement;
use Stringable;

/**
 * Class SchemaChangeManifest
 *
 * A readonly class representing a manifest of schema changes.
 *
 * @package Assegai\Orm\Migrations
 */
class SchemaChangeManifest implements Stringable
{
  /**
   * @param Entity $entity The entity for which schema changes are being made.
   * @param DDLAddStatement[] $addList An array of DDL statements to add new columns to the entity's schema.
   * @param DDLChangeStatement[] $changeList An array of DDL statements to change existing columns in the entity's schema.
   * @param DDLDropStatement[] $dropList An array of DDL statements to drop columns from the entity's schema.
   */
  public function __construct(
    public readonly Entity $entity,
    protected array $addList = [],
    protected array $changeList = [],
    protected array $dropList = [],
  )
  {}

  /**
   * Adds a new DDLAddStatement to the $addList array.
   *
   * @param DDLAddStatement $statement A DDLAddStatement to be added to the array.
   * @return void
   */
  public function add(DDLAddStatement $statement): void
  {
    $this->addList[] = $statement;
  }

  /**
   * Adds a new DDLChangeStatement to the $changeList array.
   *
   * @param DDLChangeStatement $statement A DDLChangeStatement to be added to the array.
   * @return void
   */
  public function change(DDLChangeStatement $statement): void
  {
    $this->changeList[] = $statement;
  }

  /**
   * Adds a new DDLDropStatement to the $dropList array.
   *
   * @param DDLDropStatement $statement A DDLDropStatement to be added to the array.
   * @return void
   */
  public function drop(DDLDropStatement $statement): void
  {
    $this->dropList[] = $statement;
  }

  /**
   * Returns the $addList array.
   *
   * @return array An array of DDLAddStatement objects.
   */
  public function getAddList(): array
  {
    return $this->addList;
  }

  /**
   * Returns the $changeList array.
   *
   * @return array An array of DDLChangeStatement objects.
   */
  public function getChangeList(): array
  {
    return $this->changeList;
  }

  /**
   * Returns the $dropList array.
   *
   * @return array An array of DDLChangeStatement objects.
   */
  public function getDropList(): array
  {
    return $this->dropList;
  }

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
    $dataDefinitionStatement = "ALTER TABLE $tableName ";

    foreach ($this->addList as $addDDLStatement)
    {
      $dataDefinitionStatement .= "$addDDLStatement, ";
    }

    foreach ($this->changeList as $changeDDLStatement)
    {
      $dataDefinitionStatement .= "$changeDDLStatement, ";
    }

    foreach ($this->dropList as $dropDDLStatement)
    {
      $dataDefinitionStatement .= "$dropDDLStatement, ";
    }

    return trim($dataDefinitionStatement, " \t\n\r\0\x0B,");
  }
}