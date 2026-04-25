<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Util\SqlIdentifier;
use InvalidArgumentException;

/**
 * Base SELECT builder shared across SQL-family dialects.
 *
 * The shared builder owns the common selection and aggregate rendering while
 * delegating expression creation to dialect-specific subclasses so the fluent
 * path stays typed after `all(...)`, `count(...)`, `avg(...)`, and `sum(...)`.
 */
class SQLSelectDefinition
{
  /**
   * Create a new SELECT builder.
   *
   * @param SQLQuery $query The query instance being built.
   */
  public function __construct(protected readonly SQLQuery $query)
  {
    $this->query->setQueryString(queryString: $this->buildQueryString());
  }

  /**
   * Build the initial SELECT statement prefix for the active SQL-family builder.
   *
   * @return string Returns the rendered SELECT prefix.
   */
  protected function buildQueryString(): string
  {
    return $this->buildSelectPrefix() . ' ';
  }

  /**
   * Build the SELECT prefix clause.
   *
   * @return string Returns the leading SELECT clause.
   */
  protected function buildSelectPrefix(): string
  {
    return 'SELECT';
  }

  /**
   * Select all columns or the provided column list.
   *
   * @param array $columns The columns to include in the SELECT list.
   * @return SQLSelectExpression Returns the dialect-aware select expression builder.
   */
  public function all(array $columns = []): SQLSelectExpression
  {
    return $this->createTypedExpression($this->getColumnListString(columns: $columns));
  }

  /**
   * Select a COUNT aggregate.
   *
   * @param array $columns The columns to count.
   * @return SQLSelectExpression Returns the dialect-aware select expression builder.
   */
  public function count(array $columns = []): SQLSelectExpression
  {
    return $this->createTypedExpression('COUNT(' . $this->getColumnListString(columns: $columns) . ') as total');
  }

  /**
   * Select an AVG aggregate.
   *
   * @param array $columns The columns to average.
   * @return SQLSelectExpression Returns the dialect-aware select expression builder.
   */
  public function avg(array $columns = []): SQLSelectExpression
  {
    return $this->createTypedExpression('AVG(' . $this->getColumnListString(columns: $columns) . ')');
  }

  /**
   * Select a SUM aggregate.
   *
   * @param array $columns The columns to sum.
   * @return SQLSelectExpression Returns the dialect-aware select expression builder.
   */
  public function sum(array $columns = []): SQLSelectExpression
  {
    return $this->createTypedExpression('SUM(' . $this->getColumnListString(columns: $columns) . ')');
  }

  /**
   * Append the selection fragment and create the expression builder for this dialect.
   *
   * Dialect-specific subclasses override this method when they need the fluent
   * path to continue on their own expression builder.
   *
   * @param string $selection The rendered selection fragment.
   * @return SQLSelectExpression Returns the select expression builder.
   */
  protected function createTypedExpression(string $selection): SQLSelectExpression
  {
    $this->appendSelection($selection);

    return new SQLSelectExpression(query: $this->query);
  }

  /**
   * Append the rendered selection fragment to the owning query.
   *
   * @param string $selection The rendered selection fragment.
   * @return void
   */
  protected function appendSelection(string $selection): void
  {
    $this->query->appendQueryString($selection);
  }

  /**
   * Creates and returns a list of comma-separated column names from a given
   * array of strings.
   *
   * @param array<string> $columns The list of column names.
   *
   * @return string Returns a list of comma-separated column names if the
   * given array is not empty, otherwise returns `*`.
   */
  protected function getColumnListString(array $columns): string
  {
    $columnListString = '';
    $separator = ', ';

    if (empty($columns)) {
      $columnListString .= '*';
    } else {
      foreach ($columns as $key => $value) {
        $expression = $this->formatColumnExpression((string)$value);
        $columnListString .= is_numeric($key)
          ? "{$expression}{$separator}"
          : $expression . ' AS ' . $this->query->quoteIdentifier((string)$key) . $separator;
      }
    }

    return trim($columnListString, $separator);
  }

  /**
   * Quote a selectable column expression when it is a plain identifier.
   *
   * Expressions that are not valid identifiers are returned unchanged so raw
   * SQL fragments such as functions can still be used intentionally.
   *
   * @param string $expression The column expression to format.
   * @return string Returns the quoted identifier or the original expression.
   */
  protected function formatColumnExpression(string $expression): string
  {
    if ($expression === '*') {
      return '*';
    }

    try {
      return SqlIdentifier::quote($expression, $this->query->getDialect());
    } catch (InvalidArgumentException) {
      return $expression;
    }
  }
}
