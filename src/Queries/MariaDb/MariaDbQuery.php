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
    $this->init();

    return new MariaDbAlterDefinition(query: $this);
  }

  /**
   * Begin a CREATE statement using MariaDB-specific fluent builders.
   *
   * @return MariaDbCreateDefinition Returns the MariaDB create builder.
   */
  public function create(): MariaDbCreateDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::CREATE);

    return new MariaDbCreateDefinition(query: $this);
  }

  /**
   * Begin a DROP statement using MariaDB-specific fluent builders.
   *
   * @return MariaDbDropDefinition Returns the MariaDB drop builder.
   */
  public function drop(): MariaDbDropDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::DROP);

    return new MariaDbDropDefinition(query: $this);
  }

  /**
   * Switch the active database on a MariaDB connection.
   *
   * @param string $dbName The database name to switch to.
   * @return MariaDbUseStatement Returns the MariaDB USE statement builder.
   */
  public function use(string $dbName): MariaDbUseStatement
  {
    $this->init();
    $this->setQueryType(SQLQueryType::USE);

    return new MariaDbUseStatement(query: $this, dbName: $dbName);
  }

  /**
   * Describe a MariaDB table using MariaDB-specific metadata syntax.
   *
   * @param string $subject The table or view name to describe.
   * @return MariaDbDescribeStatement Returns the MariaDB describe statement builder.
   */
  public function describe(string $subject): MariaDbDescribeStatement
  {
    $this->init();
    $this->setQueryType(SQLQueryType::DESCRIBE);

    return new MariaDbDescribeStatement(query: $this, subject: $subject);
  }

  /**
   * Begin an INSERT INTO statement.
   *
   * @param string $tableName The target table name.
   * @return MariaDbInsertIntoDefinition Returns the MariaDB insert builder.
   */
  public function insertInto(string $tableName): MariaDbInsertIntoDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::INSERT);

    return new MariaDbInsertIntoDefinition(query: $this, tableName: $tableName);
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
    $this->init();
    $this->setQueryType(SQLQueryType::DELETE);

    return new MariaDbDeleteFromStatement(query: $this, tableName: $tableName, alias: $alias);
  }

  /**
   * Truncate a table using MariaDB-specific syntax.
   *
   * @param string $tableName The table to truncate.
   * @return MariaDbTruncateStatement Returns the MariaDB truncate statement builder.
   */
  public function truncateTable(string $tableName): MariaDbTruncateStatement
  {
    $this->init();
    $this->setQueryType(SQLQueryType::TRUNCATE);

    return new MariaDbTruncateStatement(query: $this, tableName: $tableName);
  }

  /**
   * Begin a RENAME statement using MariaDB-specific fluent builders.
   *
   * @return MariaDbRenameStatement Returns the MariaDB rename builder.
   */
  public function rename(): MariaDbRenameStatement
  {
    $this->init();

    return new MariaDbRenameStatement(query: $this);
  }

  /**
   * Begin a SELECT statement.
   *
   * @return MariaDbSelectDefinition Returns the MariaDB select builder.
   */
  public function select(): MariaDbSelectDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::SELECT);

    return new MariaDbSelectDefinition(query: $this);
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
    $this->init();
    $this->setQueryType(SQLQueryType::UPDATE);

    return new MariaDbUpdateDefinition(
      query: $this,
      tableName: $tableName,
      lowPriority: $lowPriority,
      ignore: $ignore,
    );
  }
}
