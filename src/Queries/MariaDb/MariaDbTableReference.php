<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Enumerations\JoinType;
use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\MySql\MySQLTableReference;

/**
 * MariaDB-specific FROM-clause builder.
 */
class MariaDbTableReference extends MySQLTableReference
{
  /**
   * Add a LIMIT clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return MariaDbLimitClause Returns the MariaDB LIMIT-clause builder.
   */
  public function limit(int $limit, ?int $offset = null): MariaDbLimitClause
  {
    return $this->createLimitClause(limit: $limit, offset: $offset);
  }

  /**
   * Add a JOIN clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MariaDbJoinExpression Returns the MariaDB join-expression builder.
   */
  public function join(array|string $tableReferences): MariaDbJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::JOIN);
  }

  /**
   * Add a LEFT JOIN clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MariaDbJoinExpression Returns the MariaDB join-expression builder.
   */
  public function leftJoin(array|string $tableReferences): MariaDbJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::LEFT_JOIN);
  }

  /**
   * Add a RIGHT JOIN clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MariaDbJoinExpression Returns the MariaDB join-expression builder.
   */
  public function rightJoin(array|string $tableReferences): MariaDbJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::RIGHT_JOIN);
  }

  /**
   * Add an INNER JOIN clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MariaDbJoinExpression Returns the MariaDB join-expression builder.
   */
  public function innerJoin(array|string $tableReferences): MariaDbJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::INNER_JOIN);
  }

  /**
   * Add an OUTER JOIN clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MariaDbJoinExpression Returns the MariaDB join-expression builder.
   */
  public function outerJoin(array|string $tableReferences): MariaDbJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::OUTER_JOIN);
  }

  /**
   * Add a WHERE clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return MariaDbWhereClause Returns the MariaDB WHERE-clause builder.
   */
  public function where(string|array|FindOptions|FindWhereOptions $condition): MariaDbWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Add a HAVING clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param string $condition The HAVING condition to append.
   * @return MariaDbHavingClause Returns the MariaDB HAVING-clause builder.
   */
  public function having(string $condition): MariaDbHavingClause
  {
    return $this->createHavingClause(condition: $condition);
  }

  /**
   * Create the MariaDB WHERE-clause builder.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return SQLWhereClause Returns the MariaDB WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): MariaDbWhereClause
  {
    return new MariaDbWhereClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the MariaDB HAVING-clause builder.
   *
   * @param string $condition The HAVING condition to append.
   * @return SQLHavingClause Returns the MariaDB HAVING-clause builder.
   */
  protected function createHavingClause(string $condition): MariaDbHavingClause
  {
    return new MariaDbHavingClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the MariaDB join-expression builder.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @param JoinType $joinType The join type to apply.
   * @return MariaDbJoinExpression Returns the MariaDB join-expression builder.
   */
  protected function createJoinExpression(array|string $tableReferences, JoinType $joinType): MariaDbJoinExpression
  {
    return new MariaDbJoinExpression(query: $this->query, joinTableReferences: $tableReferences, joinType: $joinType);
  }

  /**
   * Create the MariaDB LIMIT-clause builder.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return MariaDbLimitClause Returns the MariaDB LIMIT-clause builder.
   */
  protected function createLimitClause(int $limit, ?int $offset = null): MariaDbLimitClause
  {
    return new MariaDbLimitClause(query: $this->query, limit: $limit, offset: $offset);
  }
}
