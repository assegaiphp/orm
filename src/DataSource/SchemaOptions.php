<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\SQLDialect;

/**
 *
 */
class SchemaOptions
{
  /**
   * @param string $dbName
   * @param SQLDialect $dialect
   * @param string|null $entityPrefix
   * @param bool $logging
   * @param bool $dropSchema
   * @param bool $synchronize
   */
  public function __construct(
    protected string $dbName = 'navigator',
    protected SQLDialect $dialect = SQLDialect::MYSQL,
    public readonly ?string $entityPrefix = null,
    public readonly bool $logging = false,
    public readonly bool $dropSchema = false,
    public readonly bool $synchronize = false,
  ) { }

  /**
   * @return string
   */
  public function dbName(): string
  {
    return $this->dbName;
  }

  /**
   * @return SQLDialect
   */
  public function dialect(): SQLDialect
  {
    return $this->dialect;
  }
}