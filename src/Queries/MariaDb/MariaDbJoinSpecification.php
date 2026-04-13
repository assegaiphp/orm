<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Enumerations\JoinType;
use Assegai\Orm\Queries\MySql\MySQLJoinSpecification;

/**
 * MariaDB-specific JOIN-specification builder.
 */
class MariaDbJoinSpecification extends MySQLJoinSpecification
{
  /**
   * Add a WHERE clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param string $condition The WHERE condition to append.
   * @return MariaDbWhereClause Returns the MariaDB WHERE-clause builder.
   */
  public function where(string $condition): MariaDbWhereClause
  {
    return $this->createWhereClause(condition: $condition);
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
   * Create the MariaDB WHERE-clause builder.
   *
   * @param string $condition The WHERE condition to append.
   * @return MariaDbWhereClause Returns the MariaDB WHERE-clause builder.
   */
  protected function createWhereClause(string $condition): MariaDbWhereClause
  {
    return new MariaDbWhereClause(query: $this->query, condition: $condition);
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
}
