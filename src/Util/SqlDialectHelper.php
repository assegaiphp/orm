<?php

namespace Assegai\Orm\Util;

use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use PDO;

final class SqlDialectHelper
{
  public static function fromDataSourceType(DataSourceType $type): SQLDialect
  {
    return match ($type) {
      DataSourceType::POSTGRESQL => SQLDialect::POSTGRESQL,
      DataSourceType::MARIADB => SQLDialect::MARIADB,
      DataSourceType::SQLITE => SQLDialect::SQLITE,
      DataSourceType::MSSQL => SQLDialect::MSSQL,
      default => SQLDialect::MYSQL,
    };
  }

  public static function fromPdo(PDO $connection): SQLDialect
  {
    return SQLDialect::fromString((string)$connection->getAttribute(PDO::ATTR_DRIVER_NAME));
  }

  public static function quoteIdentifier(string $identifier, SQLDialect $dialect): string
  {
    if ($dialect === SQLDialect::MSSQL) {
      $escaped = str_replace(']', ']]', $identifier);
      return '[' . $escaped . ']';
    }

    $quote = match ($dialect) {
      SQLDialect::POSTGRESQL, SQLDialect::SQLITE => '"',
      default => '`',
    };

    $escaped = str_replace($quote, $quote . $quote, $identifier);
    return $quote . $escaped . $quote;
  }

  public static function qualifyTable(string $tableName, ?string $databaseName, SQLDialect $dialect, ?string $schema = null): string
  {
    if (in_array($dialect, [SQLDialect::POSTGRESQL, SQLDialect::MSSQL], true) && !empty($schema)) {
      return self::quoteIdentifier($schema, $dialect) . '.' . self::quoteIdentifier($tableName, $dialect);
    }

    if (empty($databaseName) || in_array($dialect, [SQLDialect::POSTGRESQL, SQLDialect::SQLITE, SQLDialect::MSSQL], true)) {
      return self::quoteIdentifier($tableName, $dialect);
    }

    return self::quoteIdentifier($databaseName, $dialect) . '.' . self::quoteIdentifier($tableName, $dialect);
  }

  public static function normalizeSqlitePath(string $path): string
  {
    if ($path === ':memory:' || str_starts_with($path, 'file:')) {
      return $path;
    }

    $isAbsolutePath = preg_match('/^(?:[A-Za-z]:[\\\\\\/]|[\\\\\\/])/', $path) === 1;
    if ($isAbsolutePath) {
      return $path;
    }

    $cwd = getcwd() ?: '.';
    return rtrim($cwd, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
  }
}
