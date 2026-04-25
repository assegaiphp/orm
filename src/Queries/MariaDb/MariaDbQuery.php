<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLQuery;
use Assegai\Orm\Queries\Sql\SQLQueryType;

/**
 * Root MariaDB query builder.
 */
class MariaDbQuery extends MySQLQuery
{
  /**
   * Begin an ALTER statement using MariaDB-specific fluent builders.
   *
   * @return MariaDbAlterDefinition Returns the MariaDB alter builder.
   */
  public function alter(): MariaDbAlterDefinition
  {
    return parent::alter();
  }

  /**
   * Begin a CREATE statement using MariaDB-specific fluent builders.
   *
   * @return MariaDbCreateDefinition Returns the MariaDB create builder.
   */
  public function create(): MariaDbCreateDefinition
  {
    return parent::create();
  }

  /**
   * Begin a DROP statement using MariaDB-specific fluent builders.
   *
   * @return MariaDbDropDefinition Returns the MariaDB drop builder.
   */
  public function drop(): MariaDbDropDefinition
  {
    return parent::drop();
  }

  /**
   * Switch the active database on a MariaDB connection.
   *
   * @param string $dbName The database name to switch to.
   * @return MariaDbUseStatement Returns the MariaDB USE statement builder.
   */
  public function use(string $dbName): MariaDbUseStatement
  {
    return parent::use($dbName);
  }

  /**
   * Describe a MariaDB table using MariaDB-specific metadata syntax.
   *
   * @param string $subject The table or view name to describe.
   * @return MariaDbDescribeStatement Returns the MariaDB describe statement builder.
   */
  public function describe(string $subject): MariaDbDescribeStatement
  {
    return parent::describe($subject);
  }

  /**
   * Begin an INSERT INTO statement.
   *
   * @param string $tableName The target table name.
   * @return MariaDbInsertIntoDefinition Returns the MariaDB insert builder.
   */
  public function insertInto(string $tableName): MariaDbInsertIntoDefinition
  {
    return parent::insertInto($tableName);
  }

  /**
   * Begin a DELETE FROM statement.
   *
   * @param string $tableName The target table name.
   * @param string|null $alias The optional table alias.
   * @return MariaDbDeleteFromStatement Returns the MariaDB delete builder.
   */
  public function deleteFrom(string $tableName, ?string $alias = null): MariaDbDeleteFromStatement
  {
    return parent::deleteFrom($tableName, $alias);
  }

  /**
   * Truncate a table using MariaDB-specific syntax.
   *
   * @param string $tableName The table to truncate.
   * @return MariaDbTruncateStatement Returns the MariaDB truncate statement builder.
   */
  public function truncateTable(string $tableName): MariaDbTruncateStatement
  {
    return parent::truncateTable($tableName);
  }

  /**
   * Begin a RENAME statement using MariaDB-specific fluent builders.
   *
   * @return MariaDbRenameStatement Returns the MariaDB rename builder.
   */
  public function rename(): MariaDbRenameStatement
  {
    return parent::rename();
  }

  /**
   * Begin a SELECT statement.
   *
   * @return MariaDbSelectDefinition Returns the MariaDB select builder.
   */
  public function select(): MariaDbSelectDefinition
  {
    return parent::select();
  }

  /**
   * Begin an UPDATE statement.
   *
   * @param string $tableName The target table name.
   * @param bool $lowPriority Whether LOW_PRIORITY should be applied.
   * @param bool $ignore Whether IGNORE should be applied.
   * @return MariaDbUpdateDefinition Returns the MariaDB update builder.
   */
  public function update(string $tableName, bool $lowPriority = false, bool $ignore = false): MariaDbUpdateDefinition
  {
    return parent::update($tableName, $lowPriority, $ignore);
  }

  protected function createAlterDefinition(): MariaDbAlterDefinition
  {
    return new MariaDbAlterDefinition(query: $this);
  }

  protected function createCreateDefinition(): MariaDbCreateDefinition
  {
    return new MariaDbCreateDefinition(query: $this);
  }

  protected function createDropDefinition(): MariaDbDropDefinition
  {
    return new MariaDbDropDefinition(query: $this);
  }

  protected function createDescribeStatement(string $subject): MariaDbDescribeStatement
  {
    return new MariaDbDescribeStatement(query: $this, subject: $subject);
  }

  protected function createInsertIntoDefinition(string $tableName): MariaDbInsertIntoDefinition
  {
    return new MariaDbInsertIntoDefinition(query: $this, tableName: $tableName);
  }

  protected function createDeleteFromStatement(string $tableName, ?string $alias = null): MariaDbDeleteFromStatement
  {
    return new MariaDbDeleteFromStatement(query: $this, tableName: $tableName, alias: $alias);
  }

  protected function createTruncateStatement(string $tableName): MariaDbTruncateStatement
  {
    return new MariaDbTruncateStatement(query: $this, tableName: $tableName);
  }

  protected function createRenameStatement(): MariaDbRenameStatement
  {
    return new MariaDbRenameStatement(query: $this);
  }

  protected function createSelectDefinition(): MariaDbSelectDefinition
  {
    return new MariaDbSelectDefinition(query: $this);
  }

  protected function createUpdateDefinition(
    string $tableName,
    bool $lowPriority = false,
    bool $ignore = false,
  ): MariaDbUpdateDefinition
  {
    return new MariaDbUpdateDefinition(
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
   * @return MariaDbUseStatement Returns the MariaDB USE statement builder.
   */
  protected function createUseStatement(string $dbName): MariaDbUseStatement
  {
    return new MariaDbUseStatement(query: $this, dbName: $dbName);
  }
}
