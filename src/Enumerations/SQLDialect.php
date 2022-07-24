<?php

namespace Assegai\Orm\Enumerations;

enum SQLDialect: string
{
  case MYSQL = 'mysql';
  case POSTGRESQL = 'pgsql';
  case MSSQL = 'mssql';
  case MARIADB = 'mariadb';
  case SQLITE = 'sqlite';
  case UNKNOWN = 'unknown';

  /**
   * @param string $case
   * @return SQLDialect
   */
  public static function fromString(string $case): SQLDialect
  {
    return match($case) {
      'mysql' => self::MYSQL,
      'pgsql' => self::POSTGRESQL,
      'mssql' => self::MSSQL,
      'mariadb' => self::MARIADB,
      'sqlite' => self::SQLITE,
      default => self::UNKNOWN
    };
  }
}