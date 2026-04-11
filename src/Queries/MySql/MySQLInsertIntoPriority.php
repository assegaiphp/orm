<?php

namespace Assegai\Orm\Queries\MySql;

/**
 * MySQL-specific insert-priority constants.
 *
 * These values model the priority modifiers supported by the MySQL-family
 * INSERT syntax.
 */
class MySQLInsertIntoPriority
{
  public const LOW_PRIORITY = 'LOW PRIORITY';
  public const DELAYED = 'DELAYED';
  public const HIGH_PRIORITY = 'HIGH PRIORITY';
  public const HIGH = 'HIGH';
}
