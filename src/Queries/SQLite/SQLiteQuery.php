<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLQueryType;

/**
 * Root SQLite query builder.
 */
class SQLiteQuery extends SQLQuery
{
  /**
   * Begin an ALTER statement using SQLite-specific fluent builders.
   *
   * @return SQLiteAlterDefinition Returns the SQLite alter builder.
   */
  public function alter(): SQLiteAlterDefinition
  {
    $this->init();

    return new SQLiteAlterDefinition(query: $this);
  }

  /**
   * Begin a CREATE statement using SQLite-specific fluent builders.
   *
   * @return SQLiteCreateDefinition Returns the SQLite create builder.
   */
  public function create(): SQLiteCreateDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::CREATE);

    return new SQLiteCreateDefinition(query: $this);
  }

  /**
   * Begin a DROP statement using SQLite-specific fluent builders.
   *
   * @return SQLiteDropDefinition Returns the SQLite drop builder.
   */
  public function drop(): SQLiteDropDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::DROP);

    return new SQLiteDropDefinition(query: $this);
  }

  /**
   * Begin an INSERT INTO statement.
   *
   * @param string $tableName The target table name.
   * @return SQLiteInsertIntoDefinition Returns the SQLite insert builder.
   */
  public function insertInto(string $tableName): SQLiteInsertIntoDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::INSERT);

    return new SQLiteInsertIntoDefinition(query: $this, tableName: $tableName);
  }

  /**
   * Begin a SELECT statement.
   *
   * @return SQLiteSelectDefinition Returns the SQLite select builder.
   */
  public function select(): SQLiteSelectDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::SELECT);

    return new SQLiteSelectDefinition(query: $this);
  }

  /**
   * Begin a DELETE FROM statement.
   *
   * @param string $tableName The target table name.
   * @param string|null $alias The optional table alias.
   * @return SQLiteDeleteFromStatement Returns the SQLite delete builder.
   */
  public function deleteFrom(string $tableName, ?string $alias = null): SQLiteDeleteFromStatement
  {
    $this->init();
    $this->setQueryType(SQLQueryType::DELETE);

    return new SQLiteDeleteFromStatement(query: $this, tableName: $tableName, alias: $alias);
  }

  /**
   * Begin a RENAME statement using SQLite-specific fluent builders.
   *
   * @return SQLiteRenameStatement Returns the SQLite rename builder.
   */
  public function rename(): SQLiteRenameStatement
  {
    $this->init();

    return new SQLiteRenameStatement(query: $this);
  }

  /**
   * Begin an UPDATE statement.
   *
   * @param string $tableName The target table name.
   * @return SQLiteUpdateDefinition Returns the SQLite update builder.
   */
  public function update(string $tableName): SQLiteUpdateDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::UPDATE);

    return new SQLiteUpdateDefinition(query: $this, tableName: $tableName);
  }
}
