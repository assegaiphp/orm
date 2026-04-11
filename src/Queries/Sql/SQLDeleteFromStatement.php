<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Traits\ExecutableTrait;

/**
 * Base fluent builder for SQL DELETE statements.
 *
 * This builder owns the DELETE clause itself and keeps condition chaining on the
 * delete statement so dialect-specific builders can continue to expose their own
 * fluent methods after `where()`, `and()`, or `or()` are applied.
 */
class SQLDeleteFromStatement
{
  use ExecutableTrait;

  /**
   * Create a new SQL DELETE statement builder.
   *
   * @param SQLQuery $query The query instance being built.
   * @param string $tableName The table to delete from.
   * @param string|null $alias The optional table alias used by the statement.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $tableName,
    protected readonly ?string $alias = null
  )
  {
    $tableName = str_replace(['`', '"'], '', $tableName);
    $queryString = 'DELETE FROM ' . $this->query->quoteIdentifier($tableName);

    if (!is_null($alias)) {
      $queryString .= ' AS ' . $this->query->quoteIdentifier($alias);
    }

    $this->query->setQueryString(queryString: $queryString);
  }

  /**
   * Append or replace the WHERE clause for the delete statement.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile into the WHERE clause.
   * @return static Returns the current delete builder for fluent chaining.
   */
  public function where(string|array|FindOptions|FindWhereOptions $condition): static
  {
    $compiled = $this->compileCondition($condition);

    if ($compiled !== '') {
      $filtered = $this->filterConditionColumnNames($compiled);

      if (!str_contains($this->query->queryString(), 'WHERE')) {
        $this->query->appendQueryString('WHERE ' . $filtered);
      } else {
        $this->query->replaceWhereClause($compiled);
      }
    }

    return $this;
  }

  /**
   * Append an OR condition to the current WHERE clause.
   *
   * @param string $condition The raw OR condition to append.
   * @return static Returns the current delete builder for fluent chaining.
   */
  public function or(string $condition): static
  {
    $operator = $this->filterOperator('OR');
    $this->query->appendQueryString($operator . ' ' . $this->filterConditionColumnNames($condition));

    return $this;
  }

  /**
   * Append an AND condition to the current WHERE clause.
   *
   * @param string $condition The raw AND condition to append.
   * @return static Returns the current delete builder for fluent chaining.
   */
  public function and(string $condition): static
  {
    $operator = $this->filterOperator('AND');
    $this->query->appendQueryString($operator . ' ' . $condition);

    return $this;
  }

  /**
   * Convert a column selection list into SQL for dialect-specific delete clauses.
   *
   * @param array|string $columns The column names or expressions to format.
   * @return string Returns the formatted column list.
   */
  protected function getColumnListString(array|string $columns): string
  {
    if (is_string($columns)) {
      return $columns === '*'
        ? '*'
        : $this->query->quoteIdentifier(str_replace(['`', '"'], '', $columns));
    }

    $parts = [];

    foreach ($columns as $key => $value) {
      $expression = $this->formatColumnExpression((string)$value);
      $parts[] = is_numeric($key)
        ? $expression
        : $expression . ' AS ' . $this->query->quoteIdentifier((string)$key);
    }

    return implode(', ', $parts);
  }

  /**
   * Convert table references into SQL for dialect-specific delete clauses.
   *
   * @param array|string $tableReferences The table references to format.
   * @return string Returns the formatted table reference list.
   */
  protected function formatTableReferences(array|string $tableReferences): string
  {
    if (is_string($tableReferences)) {
      return $this->query->quoteIdentifier(str_replace(['`', '"'], '', $tableReferences));
    }

    $parts = [];

    foreach ($tableReferences as $alias => $reference) {
      $quotedReference = $this->query->quoteIdentifier(str_replace(['`', '"'], '', (string)$reference));
      $parts[] = is_numeric($alias)
        ? $quotedReference
        : $quotedReference . ' AS ' . $this->query->quoteIdentifier((string)$alias);
    }

    return implode(', ', $parts);
  }

  /**
   * Convert a single column expression into the current dialect's identifier format.
   *
   * @param string $expression The column expression to format.
   * @return string Returns the formatted column expression.
   */
  protected function formatColumnExpression(string $expression): string
  {
    if ($expression === '*') {
      return '*';
    }

    return $this->query->quoteIdentifier(str_replace(['`', '"'], '', $expression));
  }

  /**
   * Resolve the correct logical operator token for chained conditions.
   *
   * @param string $operator The operator being applied.
   * @return string Returns either the requested operator or WHERE for the first condition.
   */
  protected function filterOperator(string $operator): string
  {
    return str_contains($this->query->queryString(), 'WHERE') ? $operator : 'WHERE';
  }

  /**
   * Apply any condition-string filtering needed before appending SQL.
   *
   * @param string $conditions The compiled condition string.
   * @return string Returns the filtered condition string.
   */
  protected function filterConditionColumnNames(string $conditions): string
  {
    return $conditions;
  }

  /**
   * Compile supported condition inputs into a raw SQL fragment.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile.
   * @return string Returns the compiled condition string.
   */
  protected function compileCondition(string|array|FindOptions|FindWhereOptions $condition): string
  {
    if ($condition instanceof FindOptions) {
      $condition = $condition->where ?? '';
    }

    if ($condition instanceof FindWhereOptions) {
      return $condition->compile($this->query);
    }

    if (is_array($condition)) {
      return (new FindWhereOptions(conditions: $condition))->compile($this->query);
    }

    return $condition;
  }
}