<?php

namespace Assegai\Orm\Enumerations;

/**
 * Enum DefaultColumnLength. The default column lengths.
 */
enum DefaultColumnLength: int
{
  const CHAR = 30;
  const VARCHAR = 255;
  const BINARY = 1;
  const VARBINARY = 255;
  const TINYBLOB = 255;
  const BLOB = 65535;
  const TEXT = 65535;
  const MEDIUMBLOB = 16777215;
  const MEDIUMTEXT = 16777215;
  const LONGBLOB = 4294967295;
  const LONGTEXT = 4294967295;
}
