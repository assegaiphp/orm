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
}
