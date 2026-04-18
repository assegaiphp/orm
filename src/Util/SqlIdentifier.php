<?php

namespace Assegai\Orm\Util;

use Assegai\Orm\Enumerations\SQLDialect;
use InvalidArgumentException;

final class SqlIdentifier
{
  /**
   * Quotes a column or table identifier after validating each segment.
   *
   * @param string $identifier
   * @param SQLDialect $dialect
   * @return string
   */
  public static function quote(string $identifier, SQLDialect $dialect = SQLDialect::MYSQL): string
  {
    $identifier = trim($identifier);

    if ($identifier === '*') {
      return '*';
    }

    $segments = explode('.', str_replace(['`', '"', '[', ']'], '', $identifier));
    $quotedSegments = [];

    foreach ($segments as $index => $segment) {
      $segment = trim($segment);

      if ($segment === '*') {
        if ($index !== array_key_last($segments)) {
          throw new InvalidArgumentException("Unsafe SQL identifier: $identifier");
        }

        $quotedSegments[] = '*';
        continue;
      }

      if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $segment)) {
        throw new InvalidArgumentException("Unsafe SQL identifier: $identifier");
      }

      $quotedSegments[] = SqlDialectHelper::quoteIdentifier($segment, $dialect);
    }

    return implode('.', $quotedSegments);
  }
}
