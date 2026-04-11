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
    $this->init();

    return new MySQLAlterDefinition(query: $this);
  }

  /**
   * Begin a CREATE statement using MySQL-specific fluent builders.
   *
   * @return MySQLCreateDefinition Returns the MySQL create builder.
   */
  public function create(): MySQLCreateDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::CREATE);

    return new MySQLCreateDefinition(query: $this);
  }

  /**
   * Begin a DROP statement using MySQL-specific fluent builders.
   *
   * @return MySQLDropDefinition Returns the MySQL drop builder.
   */
  public function drop(): MySQLDropDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::DROP);

    return new MySQLDropDefinition(query: $this);
  }

  /**
   * Begin an INSERT INTO statement.
   *
   * @param string $tableName The target table name.
   * @return MySQLInsertIntoDefinition Returns the MySQL insert builder.
   */
  public function insertInto(string $tableName): MySQLInsertIntoDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::INSERT);

    return new MySQLInsertIntoDefinition(query: $this, tableName: $tableName);
  }

  /**
   * Begin a SELECT statement.
   *
   * @return MySQLSelectDefinition Returns the MySQL select builder.
   */
  public function select(): MySQLSelectDefinition
  {
    $this->init();
    $this->setQueryType(SQLQueryType::SELECT);

    return new MySQLSelectDefinition(query: $this);
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
    $this->init();
    $this->setQueryType(SQLQueryType::DELETE);

    return new MySQLDeleteFromStatement(query: $this, tableName: $tableName, alias: $alias);
  }

  /**
   * Begin a RENAME statement using MySQL-specific fluent builders.
   *
   * @return MySQLRenameStatement Returns the MySQL rename builder.
   */
  public function rename(): MySQLRenameStatement
  {
    $this->init();

    return new MySQLRenameStatement(query: $this);
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
    $this->init();
    $this->setQueryType(SQLQueryType::UPDATE);

    return new MySQLUpdateDefinition(
      query: $this,
      tableName: $tableName,
      lowPriority: $lowPriority,
      ignore: $ignore,
    );
  }
}
