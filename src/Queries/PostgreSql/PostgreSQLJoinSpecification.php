<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Enumerations\JoinType;
use Assegai\Orm\Queries\Sql\SQLJoinSpecification;

/**
 * PostgreSQL-specific JOIN-specification builder.
 */
class PostgreSQLJoinSpecification extends SQLJoinSpecification
{
  /**
   * Add a WHERE clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param string $condition The WHERE condition to append.
   * @return PostgreSQLWhereClause Returns the PostgreSQL WHERE-clause builder.
   */
  public function where(string $condition): PostgreSQLWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Add a JOIN clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return PostgreSQLJoinExpression Returns the PostgreSQL join-expression builder.
   */
  public function join(array|string $tableReferences): PostgreSQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::JOIN);
  }

  /**
   * Add a LEFT JOIN clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return PostgreSQLJoinExpression Returns the PostgreSQL join-expression builder.
   */
  public function leftJoin(array|string $tableReferences): PostgreSQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::LEFT_JOIN);
  }

  /**
   * Add a RIGHT JOIN clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return PostgreSQLJoinExpression Returns the PostgreSQL join-expression builder.
   */
  public function rightJoin(array|string $tableReferences): PostgreSQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::RIGHT_JOIN);
  }

  /**
   * Add an INNER JOIN clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return PostgreSQLJoinExpression Returns the PostgreSQL join-expression builder.
   */
  public function innerJoin(array|string $tableReferences): PostgreSQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::INNER_JOIN);
  }

  /**
   * Add an OUTER JOIN clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return PostgreSQLJoinExpression Returns the PostgreSQL join-expression builder.
   */
  public function outerJoin(array|string $tableReferences): PostgreSQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::OUTER_JOIN);
  }

  /**
   * Create the PostgreSQL WHERE-clause builder.
   *
   * @param string $condition The WHERE condition to append.
   * @return PostgreSQLWhereClause Returns the PostgreSQL WHERE-clause builder.
   */
  protected function createWhereClause(string $condition): PostgreSQLWhereClause
  {
    return new PostgreSQLWhereClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the PostgreSQL join-expression builder.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @param JoinType $joinType The join type to apply.
   * @return PostgreSQLJoinExpression Returns the PostgreSQL join-expression builder.
   */
  protected function createJoinExpression(array|string $tableReferences, JoinType $joinType): PostgreSQLJoinExpression
  {
    return new PostgreSQLJoinExpression(query: $this->query, joinTableReferences: $tableReferences, joinType: $joinType);
  }
}
