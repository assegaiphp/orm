<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\SQLDialect;

/**
 *
 */
readonly class SchemaOptions
{
  /**
   * @param string $dbName
   * @param SQLDialect $dialect
   * @param string|null $entityPrefix
   * @param bool $logging
   * @param bool $dropSchema
   * @param bool $synchronize
   * @param bool $checkIfExists
   * @param bool $isTemporary
   * @param SQLCharacterSet|null $characterSet
   */
  public function __construct(
    public string           $dbName = '',
    public SQLDialect       $dialect = SQLDialect::MYSQL,
    public ?string          $entityPrefix = null,
    public bool             $logging = false,
    public bool             $dropSchema = false,
    public bool             $synchronize = false,
    public bool             $checkIfExists = false,
    public bool             $isTemporary = false,
    public ?SQLCharacterSet $characterSet = null,
  ) { }
}