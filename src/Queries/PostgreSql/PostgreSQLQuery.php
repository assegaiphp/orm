<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLQueryType;

/**
 * Root PostgreSQL query builder.
 */
class PostgreSQLQuery extends SQLQuery
{
  /**
   * Begin an ALTER statement using PostgreSQL-specific fluent builders.
   *
   * @return PostgreSQLAlterDefinition Returns the PostgreSQL alter builder.
   */
  public function alter(): PostgreSQLAlterDefinition
  {
    $this->init();

    return new PostgreSQLAlterDefinition(query: $this);
  }

  /**
   * Begin a CREATE statement using PostgreSQL-specific fluent builders.
   *
   * @return PostgreSQLCreateDefinition Returns the PostgreSQL create builder.
   */
  public function create(): PostgreSQLCreateDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::CREATE);

    return new PostgreSQLCreateDefinition(query: $this);
  }

  /**
   * Begin a DROP statement using PostgreSQL-specific fluent builders.
   *
   * @return PostgreSQLDropDefinition Returns the PostgreSQL drop builder.
   */
  public function drop(): PostgreSQLDropDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::DROP);

    return new PostgreSQLDropDefinition(query: $this);
  }

  /**
   * Begin an INSERT INTO statement.
   *
   * @param string $tableName The target table name.
   * @return PostgreSQLInsertIntoDefinition Returns the PostgreSQL insert builder.
   */
  public function insertInto(string $tableName): PostgreSQLInsertIntoDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::INSERT);

    return new PostgreSQLInsertIntoDefinition(query: $this, tableName: $tableName);
  }

  /**
   * Begin a SELECT statement.
   *
   * @return PostgreSQLSelectDefinition Returns the PostgreSQL select builder.
   */
  public function select(): PostgreSQLSelectDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::SELECT);

    return new PostgreSQLSelectDefinition(query: $this);
  }

  /**
   * Begin a DELETE FROM statement.
   *
   * @param string $tableName The target table name.
   * @param string|null $alias The optional table alias.
   * @return PostgreSQLDeleteFromStatement Returns the PostgreSQL delete builder.
   */
  public function deleteFrom(string $tableName, ?string $alias = null): PostgreSQLDeleteFromStatement
  {
    $this->init();
    $this->setQueryType(SQLQueryType::DELETE);

    return new PostgreSQLDeleteFromStatement(query: $this, tableName: $tableName, alias: $alias);
  }

  /**
   * Begin a RENAME statement using PostgreSQL-specific fluent builders.
   *
   * @return PostgreSQLRenameStatement Returns the PostgreSQL rename builder.
   */
  public function rename(): PostgreSQLRenameStatement
  {
    $this->init();

    return new PostgreSQLRenameStatement(query: $this);
  }

  /**
   * Begin an UPDATE statement.
   *
   * @param string $tableName The target table name.
   * @return PostgreSQLUpdateDefinition Returns the PostgreSQL update builder.
   */
  public function update(string $tableName): PostgreSQLUpdateDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::UPDATE);

    return new PostgreSQLUpdateDefinition(query: $this, tableName: $tableName);
  }
}
