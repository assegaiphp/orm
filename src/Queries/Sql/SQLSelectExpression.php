<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Base SELECT expression builder shared across SQL-family dialects.
 *
 * This class keeps the common `from(...)` behaviour while allowing
 * dialect-specific subclasses to keep the fluent chain typed after
 * `select()->all(...)` or aggregate selection calls.
 */
class SQLSelectExpression
{
  /**
   * Create a new shared SELECT expression builder.
   *
   * @param SQLQuery $query The query being built.
   */
  public function __construct(protected readonly SQLQuery $query) { }

  /**
   * Add the FROM clause to the current SELECT statement.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLTableReference Returns the shared table reference builder.
   */
  public function from(array|string $tableReferences): SQLTableReference
  {
    return $this->createTableReference(tableReferences: $tableReferences);
  }

  /**
   * Create the table reference builder used by this SELECT expression.
   *
   * Dialect-specific subclasses override this method to keep the fluent
   * chain on their own typed table-reference builders.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLTableReference Returns the table reference builder.
   */
  protected function createTableReference(array|string $tableReferences): SQLTableReference
  {
    return new SQLTableReference(query: $this->query, tableReferences: $tableReferences);
  }
}
