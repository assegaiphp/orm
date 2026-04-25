<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLDeleteFromStatement;

/**
 * MySQL-specific DELETE builder.
 *
 * This builder exposes MySQL-only DELETE modifiers while keeping the shared
 * condition chaining behaviour from the base SQL delete builder.
 */
class MySQLDeleteFromStatement extends SQLDeleteFromStatement
{
  /**
   * Mark the DELETE statement as LOW_PRIORITY.
   *
   * @return self Returns the current delete builder for fluent chaining.
   */
  public function lowPriority(): self
  {
    $this->applyModifier('LOW_PRIORITY');

    return $this;
  }

  /**
   * Mark the DELETE statement as QUICK.
   *
   * @return self Returns the current delete builder for fluent chaining.
   */
  public function quick(): self
  {
    $this->applyModifier('QUICK');

    return $this;
  }

  /**
   * Mark the DELETE statement as IGNORE.
   *
   * @return self Returns the current delete builder for fluent chaining.
   */
  public function ignore(): self
  {
    $this->applyModifier('IGNORE');

    return $this;
  }

  /**
   * Apply a MySQL DELETE modifier without dropping existing modifiers.
   *
   * @param string $modifier The modifier keyword to add.
   * @return void
   */
  protected function applyModifier(string $modifier): void
  {
    $queryString = $this->query->queryString();

    if (str_contains($queryString, ' ' . $modifier . ' ')) {
      return;
    }

    $existing = [];

    if (!str_starts_with($queryString, 'DELETE FROM ')) {
      if (preg_match('/^DELETE\s+(.+?)\s+FROM\b/', $queryString, $matches) === 1) {
        $existing = preg_split('/\s+/', trim($matches[1])) ?: [];
      }
    }

    $existing[] = $modifier;
    $existing = array_values(array_unique(array_filter($existing)));

    $replacement = 'DELETE ' . implode(' ', $existing) . ' FROM';
    $updated = preg_replace('/^DELETE(?:\s+(?:LOW_PRIORITY|QUICK|IGNORE))*\s+FROM/', $replacement, $queryString, 1);

    if (is_string($updated)) {
      $this->query->setQueryString($updated);
    }
  }
}