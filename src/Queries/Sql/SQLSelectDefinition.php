<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Util\SqlIdentifier;
use InvalidArgumentException;

class SQLSelectDefinition
{
  /**
   * @param SQLQuery $query
   */
  public function __construct(protected readonly SQLQuery $query)
  {
    $queryString = 'SELECT ';
    $this->query->setQueryString(queryString: $queryString);
  }

  /**
   * @param array $columns
   * @return SQLSelectExpression
   */
  public function all(array $columns = []): SQLSelectExpression
  {
    $queryString = $this->getColumnListString(columns: $columns);

    $this->query->appendQueryString($queryString);

    return new SQLSelectExpression( query: $this->query );
  }

  /**
   * @param array $columns
   * @return SQLSelectExpression
   */
  public function count(array $columns = []): SQLSelectExpression
  {
    $queryString = 'COUNT(' . $this->getColumnListString(columns: $columns) . ') as total';

    $this->query->appendQueryString($queryString);

    return new SQLSelectExpression( query: $this->query );
  }

  /**
   * @param array $columns
   * @return SQLSelectExpression
   */
  public function avg(array $columns = []): SQLSelectExpression
  {
    $queryString = 'AVG(' . $this->getColumnListString(columns: $columns) . ')';

    $this->query->appendQueryString($queryString);

    return new SQLSelectExpression( query: $this->query );
  }

  /**
   * @param array $columns
   * @return SQLSelectExpression
   */
  public function sum(array $columns = []): SQLSelectExpression
  {
    $queryString = 'SUM(' . $this->getColumnListString(columns: $columns) . ')';

    $this->query->appendQueryString($queryString);

    return new SQLSelectExpression( query: $this->query );
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
