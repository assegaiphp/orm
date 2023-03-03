<?php

namespace Assegai\Orm\Metadata;

readonly class SchemaMetadata
{
  /**
   * @param SQLTableDescription[] $tableFields
   * @param string $ddlStatement
   */
  public function __construct(
    public array  $tableFields,
    public string $ddlStatement
  )
  {
  }
}