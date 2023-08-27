<?php

namespace Assegai\Orm\Enumerations;

/**
 * Enum ColumnExtra. The extra column attributes.
 */
enum ColumnExtra: string
{
  case SIGNED = 'SIGNED';
  case UNSIGNED = 'UNSIGNED';
  case ZEROFILL = 'ZEROFILL';
  case NOW = 'NOW';
  case CURRENT_DATE = 'CURRENT_DATE()';
  case CURRENT_TIME = 'CURRENT_TIME()';
  case CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
}
