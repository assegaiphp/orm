<?php

namespace Assegai\Orm\Metadata;

/**
 * Represents metadata about a database schema.
 */
readonly class SchemaMetadata
{
  /**
   * Constructs a new SchemaMetadata object.
   *
   * @param SQLTableDescription[] $tableFields An array of SQLTableDescription objects containing information about each table in the schema.
   * @param string $ddlStatement The DDL statement that was used to create the schema.
   */
  public function __construct(
    public array  $tableFields,
    public string $ddlStatement
  )
  {
  }
}