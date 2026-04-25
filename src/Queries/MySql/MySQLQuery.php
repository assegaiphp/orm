<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLQueryType;

/**
 * Root MySQL query builder.
 */
class MySQLQuery extends SQLQuery
{
  /**
   * Begin an ALTER statement using MySQL-specific fluent builders.
   *
   * @return MySQLAlterDefinition Returns the MySQL alter builder.
   */
  public function alter(): MySQLAlterDefinition
  {
    return parent::alter();
  }

  /**
   * Begin a CREATE statement using MySQL-specific fluent builders.
   *
   * @return MySQLCreateDefinition Returns the MySQL create builder.
   */
  public function create(): MySQLCreateDefinition
  {
    return parent::create();
  }

  /**
   * Begin a DROP statement using MySQL-specific fluent builders.
   *
   * @return MySQLDropDefinition Returns the MySQL drop builder.
   */
  public function drop(): MySQLDropDefinition
  {
    return parent::drop();
  }

  /**
   * Switch the active database on a MySQL connection.
   *
   * @param string $dbName The database name to switch to.
   * @return MySQLUseStatement Returns the MySQL USE statement builder.
   */
  public function use(string $dbName): MySQLUseStatement
  {
    $this->beginRootQuery(SQLQueryType::USE);

    return $this->createUseStatement($dbName);
  }

  /**
   * Describe a MySQL table using MySQL-specific metadata syntax.
   *
   * @param string $subject The table or view name to describe.
   * @return MySQLDescribeStatement Returns the MySQL describe statement builder.
   */
  public function describe(string $subject): MySQLDescribeStatement
  {
    return parent::describe($subject);
  }

  /**
   * Begin an INSERT INTO statement.
   *
   * @param string $tableName The target table name.
   * @return MySQLInsertIntoDefinition Returns the MySQL insert builder.
   */
  public function insertInto(string $tableName): MySQLInsertIntoDefinition
  {
    return parent::insertInto($tableName);
  }

  /**
   * Begin a SELECT statement.
   *
   * @return MySQLSelectDefinition Returns the MySQL select builder.
   */
  public function select(): MySQLSelectDefinition
  {
    return parent::select();
  }

  /**
   * Begin a DELETE FROM statement.
   *
   * @param string $tableName The target table name.
   * @param string|null $alias The optional table alias.
   * @return MySQLDeleteFromStatement Returns the MySQL delete builder.
   */
  public function deleteFrom(string $tableName, ?string $alias = null): MySQLDeleteFromStatement
  {
    return parent::deleteFrom($tableName, $alias);
  }

  /**
   * Truncate a table using MySQL-specific syntax.
   *
   * @param string $tableName The table to truncate.
   * @return MySQLTruncateStatement Returns the MySQL truncate statement builder.
   */
  public function truncateTable(string $tableName): MySQLTruncateStatement
  {
    return parent::truncateTable($tableName);
  }

  /**
   * Begin a RENAME statement using MySQL-specific fluent builders.
   *
   * @return MySQLRenameStatement Returns the MySQL rename builder.
   */
  public function rename(): MySQLRenameStatement
  {
    return parent::rename();
  }

  /**
   * Begin an UPDATE statement.
   *
   * @param string $tableName The target table name.
   * @param bool $lowPriority Whether LOW_PRIORITY should be applied.
   * @param bool $ignore Whether IGNORE should be applied.
   * @return MySQLUpdateDefinition Returns the MySQL update builder.
   */
  public function update(string $tableName, bool $lowPriority = false, bool $ignore = false): MySQLUpdateDefinition
  {
    $this->beginRootQuery(SQLQueryType::UPDATE);

    return $this->createUpdateDefinition($tableName, $lowPriority, $ignore);
  }

  /**
   * Create the ALTER builder for this dialect root.
   *
   * @return MySQLAlterDefinition Returns the MySQL alter builder.
   */
  protected function createAlterDefinition(): MySQLAlterDefinition
  {
    return new MySQLAlterDefinition(query: $this);
  }

  /**
   * Create the CREATE builder for this dialect root.
   *
   * @return MySQLCreateDefinition Returns the MySQL create builder.
   */
  protected function createCreateDefinition(): MySQLCreateDefinition
  {
    return new MySQLCreateDefinition(query: $this);
  }

  /**
   * Create the DROP builder for this dialect root.
   *
   * @return MySQLDropDefinition Returns the MySQL drop builder.
   */
  protected function createDropDefinition(): MySQLDropDefinition
  {
    return new MySQLDropDefinition(query: $this);
  }

  /**
   * Create the DESCRIBE builder for this dialect root.
   *
   * @param string $subject The table or view name to describe.
   * @return MySQLDescribeStatement Returns the MySQL describe builder.
   */
  protected function createDescribeStatement(string $subject): MySQLDescribeStatement
  {
    return new MySQLDescribeStatement(query: $this, subject: $subject);
  }

  /**
   * Create the INSERT builder for this dialect root.
   *
   * @param string $tableName The target table name.
   * @return MySQLInsertIntoDefinition Returns the MySQL insert builder.
   */
  protected function createInsertIntoDefinition(string $tableName): MySQLInsertIntoDefinition
  {
    return new MySQLInsertIntoDefinition(query: $this, tableName: $tableName);
  }

  /**
   * Create the SELECT builder for this dialect root.
   *
   * @return MySQLSelectDefinition Returns the MySQL select builder.
   */
  protected function createSelectDefinition(): MySQLSelectDefinition
  {
    return new MySQLSelectDefinition(query: $this);
  }

  /**
   * Create the DELETE builder for this dialect root.
   *
   * @param string $tableName The target table name.
   * @param string|null $alias The optional table alias.
   * @return MySQLDeleteFromStatement Returns the MySQL delete builder.
   */
  protected function createDeleteFromStatement(string $tableName, ?string $alias = null): MySQLDeleteFromStatement
  {
    return new MySQLDeleteFromStatement(query: $this, tableName: $tableName, alias: $alias);
  }

  /**
   * Create the TRUNCATE builder for this dialect root.
   *
   * @param string $tableName The table to truncate.
   * @return MySQLTruncateStatement Returns the MySQL truncate builder.
   */
  protected function createTruncateStatement(string $tableName): MySQLTruncateStatement
  {
    return new MySQLTruncateStatement(query: $this, tableName: $tableName);
  }

  /**
   * Create the RENAME builder for this dialect root.
   *
   * @return MySQLRenameStatement Returns the MySQL rename builder.
   */
  protected function createRenameStatement(): MySQLRenameStatement
  {
    return new MySQLRenameStatement(query: $this);
  }

  /**
   * Create the UPDATE builder for this dialect root.
   *
   * @param string $tableName The target table name.
   * @param bool $lowPriority Whether LOW_PRIORITY should be applied.
   * @param bool $ignore Whether IGNORE should be applied.
   * @return MySQLUpdateDefinition Returns the MySQL update builder.
   */
  protected function createUpdateDefinition(
    string $tableName,
    bool $lowPriority = false,
    bool $ignore = false,
  ): MySQLUpdateDefinition
  {
    return new MySQLUpdateDefinition(
      query: $this,
      tableName: $tableName,
      lowPriority: $lowPriority,
      ignore: $ignore,
    );
  }

  /**
   * Create the USE builder for this dialect root.
   *
   * @param string $dbName The database name to switch to.
   * @return MySQLUseStatement Returns the MySQL USE statement builder.
   */
  protected function createUseStatement(string $dbName): MySQLUseStatement
  {
    return new MySQLUseStatement(query: $this, dbName: $dbName);
  }
}
