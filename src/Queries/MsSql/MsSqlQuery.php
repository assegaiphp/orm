<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLQueryType;

/**
 * Root Microsoft SQL Server query builder.
 */
class MsSqlQuery extends SQLQuery
{
  /**
   * Begin an ALTER statement using MSSQL-specific fluent builders.
   *
   * @return MsSqlAlterDefinition Returns the MSSQL alter builder.
   */
  public function alter(): MsSqlAlterDefinition
  {
    return parent::alter();
  }

  /**
   * Begin a CREATE statement using MSSQL-specific fluent builders.
   *
   * @return MsSqlCreateDefinition Returns the MSSQL create builder.
   */
  public function create(): MsSqlCreateDefinition
  {
    return parent::create();
  }

  /**
   * Begin a DROP statement using MSSQL-specific fluent builders.
   *
   * @return MsSqlDropDefinition Returns the MSSQL drop builder.
   */
  public function drop(): MsSqlDropDefinition
  {
    return parent::drop();
  }

  /**
   * Switch the active database on a SQL Server connection.
   *
   * @param string $dbName The database name to switch to.
   * @return MsSqlUseStatement Returns the MSSQL USE statement builder.
   */
  public function use(string $dbName): MsSqlUseStatement
  {
    $this->beginRootQuery(SQLQueryType::USE);

    return $this->createUseStatement($dbName);
  }

  /**
   * Describe a SQL Server table using SQL Server-specific metadata syntax.
   *
   * @param string $subject The table or view name to describe.
   * @return MsSqlDescribeStatement Returns the MSSQL describe statement builder.
   */
  public function describe(string $subject): MsSqlDescribeStatement
  {
    return parent::describe($subject);
  }

  /**
   * Begin an INSERT INTO statement.
   *
   * @param string $tableName The target table name.
   * @return MsSqlInsertIntoDefinition Returns the MSSQL insert builder.
   */
  public function insertInto(string $tableName): MsSqlInsertIntoDefinition
  {
    return parent::insertInto($tableName);
  }

  /**
   * Begin a SELECT statement.
   *
   * @return MsSqlSelectDefinition Returns the MSSQL select builder.
   */
  public function select(): MsSqlSelectDefinition
  {
    return parent::select();
  }

  /**
   * Begin a DELETE FROM statement.
   *
   * @param string $tableName The target table name.
   * @param string|null $alias The optional table alias.
   * @return MsSqlDeleteFromStatement Returns the MSSQL delete builder.
   */
  public function deleteFrom(string $tableName, ?string $alias = null): MsSqlDeleteFromStatement
  {
    return parent::deleteFrom($tableName, $alias);
  }

  /**
   * Truncate a table using SQL Server syntax.
   *
   * @param string $tableName The table to truncate.
   * @return MsSqlTruncateStatement Returns the MSSQL truncate statement builder.
   */
  public function truncateTable(string $tableName): MsSqlTruncateStatement
  {
    return parent::truncateTable($tableName);
  }

  /**
   * Begin a RENAME statement using MSSQL-specific fluent builders.
   *
   * @return MsSqlRenameStatement Returns the MSSQL rename builder.
   */
  public function rename(): MsSqlRenameStatement
  {
    return parent::rename();
  }

  /**
   * Begin an UPDATE statement.
   *
   * @param string $tableName The target table name.
   * @return MsSqlUpdateDefinition Returns the MSSQL update builder.
   */
  public function update(string $tableName): MsSqlUpdateDefinition
  {
    return parent::update($tableName);
  }

  /**
   * Create the ALTER builder for this dialect root.
   *
   * @return MsSqlAlterDefinition Returns the MSSQL alter builder.
   */
  protected function createAlterDefinition(): MsSqlAlterDefinition
  {
    return new MsSqlAlterDefinition(query: $this);
  }

  /**
   * Create the CREATE builder for this dialect root.
   *
   * @return MsSqlCreateDefinition Returns the MSSQL create builder.
   */
  protected function createCreateDefinition(): MsSqlCreateDefinition
  {
    return new MsSqlCreateDefinition(query: $this);
  }

  /**
   * Create the DROP builder for this dialect root.
   *
   * @return MsSqlDropDefinition Returns the MSSQL drop builder.
   */
  protected function createDropDefinition(): MsSqlDropDefinition
  {
    return new MsSqlDropDefinition(query: $this);
  }

  /**
   * Create the DESCRIBE builder for this dialect root.
   *
   * @param string $subject The table or view name to describe.
   * @return MsSqlDescribeStatement Returns the MSSQL describe builder.
   */
  protected function createDescribeStatement(string $subject): MsSqlDescribeStatement
  {
    return new MsSqlDescribeStatement(query: $this, subject: $subject);
  }

  /**
   * Create the INSERT builder for this dialect root.
   *
   * @param string $tableName The target table name.
   * @return MsSqlInsertIntoDefinition Returns the MSSQL insert builder.
   */
  protected function createInsertIntoDefinition(string $tableName): MsSqlInsertIntoDefinition
  {
    return new MsSqlInsertIntoDefinition(query: $this, tableName: $tableName);
  }

  /**
   * Create the SELECT builder for this dialect root.
   *
   * @return MsSqlSelectDefinition Returns the MSSQL select builder.
   */
  protected function createSelectDefinition(): MsSqlSelectDefinition
  {
    return new MsSqlSelectDefinition(query: $this);
  }

  /**
   * Create the DELETE builder for this dialect root.
   *
   * @param string $tableName The target table name.
   * @param string|null $alias The optional table alias.
   * @return MsSqlDeleteFromStatement Returns the MSSQL delete builder.
   */
  protected function createDeleteFromStatement(string $tableName, ?string $alias = null): MsSqlDeleteFromStatement
  {
    return new MsSqlDeleteFromStatement(query: $this, tableName: $tableName, alias: $alias);
  }

  /**
   * Create the TRUNCATE builder for this dialect root.
   *
   * @param string $tableName The table to truncate.
   * @return MsSqlTruncateStatement Returns the MSSQL truncate builder.
   */
  protected function createTruncateStatement(string $tableName): MsSqlTruncateStatement
  {
    return new MsSqlTruncateStatement(query: $this, tableName: $tableName);
  }

  /**
   * Create the RENAME builder for this dialect root.
   *
   * @return MsSqlRenameStatement Returns the MSSQL rename builder.
   */
  protected function createRenameStatement(): MsSqlRenameStatement
  {
    return new MsSqlRenameStatement(query: $this);
  }

  /**
   * Create the UPDATE builder for this dialect root.
   *
   * @param string $tableName The target table name.
   * @param bool $lowPriority Unused for MSSQL.
   * @param bool $ignore Unused for MSSQL.
   * @return MsSqlUpdateDefinition Returns the MSSQL update builder.
   */
  protected function createUpdateDefinition(
    string $tableName,
    bool $lowPriority = false,
    bool $ignore = false,
  ): MsSqlUpdateDefinition
  {
    return new MsSqlUpdateDefinition(query: $this, tableName: $tableName);
  }

  /**
   * Create the USE builder for this dialect root.
   *
   * @param string $dbName The database name to switch to.
   * @return MsSqlUseStatement Returns the MSSQL USE statement builder.
   */
  protected function createUseStatement(string $dbName): MsSqlUseStatement
  {
    return new MsSqlUseStatement(query: $this, dbName: $dbName);
  }
}
