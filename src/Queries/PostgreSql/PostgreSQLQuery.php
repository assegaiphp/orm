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
    return parent::alter();
  }

  /**
   * Begin a CREATE statement using PostgreSQL-specific fluent builders.
   *
   * @return PostgreSQLCreateDefinition Returns the PostgreSQL create builder.
   */
  public function create(): PostgreSQLCreateDefinition
  {
    return parent::create();
  }

  /**
   * Begin a DROP statement using PostgreSQL-specific fluent builders.
   *
   * @return PostgreSQLDropDefinition Returns the PostgreSQL drop builder.
   */
  public function drop(): PostgreSQLDropDefinition
  {
    return parent::drop();
  }

  /**
   * Describe a PostgreSQL table using PostgreSQL-specific metadata syntax.
   *
   * @param string $subject The table or view name to describe.
   * @return PostgreSQLDescribeStatement Returns the PostgreSQL describe statement builder.
   */
  public function describe(string $subject): PostgreSQLDescribeStatement
  {
    return parent::describe($subject);
  }

  /**
   * Begin an INSERT INTO statement.
   *
   * @param string $tableName The target table name.
   * @return PostgreSQLInsertIntoDefinition Returns the PostgreSQL insert builder.
   */
  public function insertInto(string $tableName): PostgreSQLInsertIntoDefinition
  {
    return parent::insertInto($tableName);
  }

  /**
   * Begin a SELECT statement.
   *
   * @return PostgreSQLSelectDefinition Returns the PostgreSQL select builder.
   */
  public function select(): PostgreSQLSelectDefinition
  {
    return parent::select();
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
    return parent::deleteFrom($tableName, $alias);
  }

  /**
   * Truncate a table using PostgreSQL-specific syntax.
   *
   * @param string $tableName The table to truncate.
   * @return PostgreSQLTruncateStatement Returns the PostgreSQL truncate statement builder.
   */
  public function truncateTable(string $tableName): PostgreSQLTruncateStatement
  {
    return parent::truncateTable($tableName);
  }

  /**
   * Begin a RENAME statement using PostgreSQL-specific fluent builders.
   *
   * @return PostgreSQLRenameStatement Returns the PostgreSQL rename builder.
   */
  public function rename(): PostgreSQLRenameStatement
  {
    return parent::rename();
  }

  /**
   * Begin an UPDATE statement.
   *
   * @param string $tableName The target table name.
   * @return PostgreSQLUpdateDefinition Returns the PostgreSQL update builder.
   */
  public function update(string $tableName): PostgreSQLUpdateDefinition
  {
    return parent::update($tableName);
  }

  protected function createAlterDefinition(): PostgreSQLAlterDefinition
  {
    return new PostgreSQLAlterDefinition(query: $this);
  }

  protected function createCreateDefinition(): PostgreSQLCreateDefinition
  {
    return new PostgreSQLCreateDefinition(query: $this);
  }

  protected function createDropDefinition(): PostgreSQLDropDefinition
  {
    return new PostgreSQLDropDefinition(query: $this);
  }

  protected function createDescribeStatement(string $subject): PostgreSQLDescribeStatement
  {
    return new PostgreSQLDescribeStatement(query: $this, subject: $subject);
  }

  protected function createInsertIntoDefinition(string $tableName): PostgreSQLInsertIntoDefinition
  {
    return new PostgreSQLInsertIntoDefinition(query: $this, tableName: $tableName);
  }

  protected function createSelectDefinition(): PostgreSQLSelectDefinition
  {
    return new PostgreSQLSelectDefinition(query: $this);
  }

  protected function createDeleteFromStatement(string $tableName, ?string $alias = null): PostgreSQLDeleteFromStatement
  {
    return new PostgreSQLDeleteFromStatement(query: $this, tableName: $tableName, alias: $alias);
  }

  protected function createTruncateStatement(string $tableName): PostgreSQLTruncateStatement
  {
    return new PostgreSQLTruncateStatement(query: $this, tableName: $tableName);
  }

  protected function createRenameStatement(): PostgreSQLRenameStatement
  {
    return new PostgreSQLRenameStatement(query: $this);
  }

  protected function createUpdateDefinition(
    string $tableName,
    bool $lowPriority = false,
    bool $ignore = false,
  ): PostgreSQLUpdateDefinition
  {
    return new PostgreSQLUpdateDefinition(query: $this, tableName: $tableName);
  }
}
