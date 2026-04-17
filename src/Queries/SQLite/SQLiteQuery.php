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
    return parent::alter();
  }

  /**
   * Begin a CREATE statement using SQLite-specific fluent builders.
   *
   * @return SQLiteCreateDefinition Returns the SQLite create builder.
   */
  public function create(): SQLiteCreateDefinition
  {
    return parent::create();
  }

  /**
   * Begin a DROP statement using SQLite-specific fluent builders.
   *
   * @return SQLiteDropDefinition Returns the SQLite drop builder.
   */
  public function drop(): SQLiteDropDefinition
  {
    return parent::drop();
  }

  /**
   * Describe a SQLite table using SQLite-specific metadata syntax.
   *
   * @param string $subject The table or view name to describe.
   * @return SQLiteDescribeStatement Returns the SQLite describe statement builder.
   */
  public function describe(string $subject): SQLiteDescribeStatement
  {
    return parent::describe($subject);
  }

  /**
   * Begin an INSERT INTO statement.
   *
   * @param string $tableName The target table name.
   * @return SQLiteInsertIntoDefinition Returns the SQLite insert builder.
   */
  public function insertInto(string $tableName): SQLiteInsertIntoDefinition
  {
    return parent::insertInto($tableName);
  }

  /**
   * Begin a SELECT statement.
   *
   * @return SQLiteSelectDefinition Returns the SQLite select builder.
   */
  public function select(): SQLiteSelectDefinition
  {
    return parent::select();
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
    return parent::deleteFrom($tableName, $alias);
  }

  /**
   * Remove all rows from a SQLite table using the closest supported syntax.
   *
   * @param string $tableName The table to clear.
   * @return SQLiteTruncateStatement Returns the SQLite truncate statement builder.
   */
  public function truncateTable(string $tableName): SQLiteTruncateStatement
  {
    return parent::truncateTable($tableName);
  }

  /**
   * Begin a RENAME statement using SQLite-specific fluent builders.
   *
   * @return SQLiteRenameStatement Returns the SQLite rename builder.
   */
  public function rename(): SQLiteRenameStatement
  {
    return parent::rename();
  }

  /**
   * Begin an UPDATE statement.
   *
   * @param string $tableName The target table name.
   * @return SQLiteUpdateDefinition Returns the SQLite update builder.
   */
  public function update(string $tableName): SQLiteUpdateDefinition
  {
    return parent::update($tableName);
  }

  protected function createAlterDefinition(): SQLiteAlterDefinition
  {
    return new SQLiteAlterDefinition(query: $this);
  }

  protected function createCreateDefinition(): SQLiteCreateDefinition
  {
    return new SQLiteCreateDefinition(query: $this);
  }

  protected function createDropDefinition(): SQLiteDropDefinition
  {
    return new SQLiteDropDefinition(query: $this);
  }

  protected function createDescribeStatement(string $subject): SQLiteDescribeStatement
  {
    return new SQLiteDescribeStatement(query: $this, subject: $subject);
  }

  protected function createInsertIntoDefinition(string $tableName): SQLiteInsertIntoDefinition
  {
    return new SQLiteInsertIntoDefinition(query: $this, tableName: $tableName);
  }

  protected function createSelectDefinition(): SQLiteSelectDefinition
  {
    return new SQLiteSelectDefinition(query: $this);
  }

  protected function createDeleteFromStatement(string $tableName, ?string $alias = null): SQLiteDeleteFromStatement
  {
    return new SQLiteDeleteFromStatement(query: $this, tableName: $tableName, alias: $alias);
  }

  protected function createTruncateStatement(string $tableName): SQLiteTruncateStatement
  {
    return new SQLiteTruncateStatement(query: $this, tableName: $tableName);
  }

  protected function createRenameStatement(): SQLiteRenameStatement
  {
    return new SQLiteRenameStatement(query: $this);
  }

  protected function createUpdateDefinition(
    string $tableName,
    bool $lowPriority = false,
    bool $ignore = false,
  ): SQLiteUpdateDefinition
  {
    return new SQLiteUpdateDefinition(query: $this, tableName: $tableName);
  }
}
