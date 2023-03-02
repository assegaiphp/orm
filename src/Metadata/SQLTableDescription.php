<?php

namespace Assegai\Orm\Metadata;

/**
 * The SQLTableDescription class represents the description of a table's column in a database.
 * It contains information such as the column name, data type, whether it allows null values,
 * if it's a key, the default value, and any additional information.
 */
class SQLTableDescription
{
  /** @var string|null The name of the field. */
  public ?string $Field = '';

  /** @var string|null The data type of the field. */
  public ?string $Type = '';

  /** @var string|null Whether the field can contain null values. */
  public ?string $Null = '';

  /** @var string|null The type of index on the field, if any. */
  public ?string $Key = '';

  /** @var string|null The default value of the field. */
  public ?string $Default = '';

  /** @var string|null Any additional information about the field. */
  public ?string $Extra = '';
}