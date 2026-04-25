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

  public static function quoteCompositeIdentifier(string $identifier, SQLDialect $dialect): string
  {
    $segments = array_values(array_filter(array_map('trim', explode('.', $identifier)), static fn(string $segment): bool => $segment !== ''));

    if ($segments === []) {
      return self::quoteIdentifier($identifier, $dialect);
    }

    return implode('.', array_map(static fn(string $segment): string => self::quoteIdentifier($segment, $dialect), $segments));
  }

  public static function unqualifyIdentifier(string $identifier): string
  {
    $segments = array_values(array_filter(array_map('trim', explode('.', $identifier)), static fn(string $segment): bool => $segment !== ''));
    $target = $segments === [] ? $identifier : end($segments);

    return trim((string) $target, "[]`\" \t\n\r\0\x0B");
  }

  public static function qualifyTable(string $tableName, ?string $databaseName, SQLDialect $dialect, ?string $schema = null): string
  {
    if (str_contains($tableName, '.')) {
      return self::quoteCompositeIdentifier($tableName, $dialect);
    }

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
