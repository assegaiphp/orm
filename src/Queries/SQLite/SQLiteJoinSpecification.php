<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Enumerations\JoinType;
use Assegai\Orm\Queries\Sql\SQLJoinSpecification;

/**
 * SQLite-specific JOIN-specification builder.
 */
class SQLiteJoinSpecification extends SQLJoinSpecification
{
  /**
   * Add a WHERE clause and keep the fluent chain on the SQLite builder path.
   *
   * @param string $condition The WHERE condition to append.
   * @return SQLiteWhereClause Returns the SQLite WHERE-clause builder.
   */
  public function where(string $condition): SQLiteWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Add a JOIN clause and keep the fluent chain on the SQLite builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLiteJoinExpression Returns the SQLite join-expression builder.
   */
  public function join(array|string $tableReferences): SQLiteJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::JOIN);
  }

  /**
   * Add a LEFT JOIN clause and keep the fluent chain on the SQLite builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLiteJoinExpression Returns the SQLite join-expression builder.
   */
  public function leftJoin(array|string $tableReferences): SQLiteJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::LEFT_JOIN);
  }

  /**
   * Add a RIGHT JOIN clause and keep the fluent chain on the SQLite builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLiteJoinExpression Returns the SQLite join-expression builder.
   */
  public function rightJoin(array|string $tableReferences): SQLiteJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::RIGHT_JOIN);
  }

  /**
   * Add an INNER JOIN clause and keep the fluent chain on the SQLite builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLiteJoinExpression Returns the SQLite join-expression builder.
   */
  public function innerJoin(array|string $tableReferences): SQLiteJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::INNER_JOIN);
  }

  /**
   * Add an OUTER JOIN clause and keep the fluent chain on the SQLite builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLiteJoinExpression Returns the SQLite join-expression builder.
   */
  public function outerJoin(array|string $tableReferences): SQLiteJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::OUTER_JOIN);
  }

  /**
   * Create the SQLite WHERE-clause builder.
   *
   * @param string $condition The WHERE condition to append.
   * @return SQLiteWhereClause Returns the SQLite WHERE-clause builder.
   */
  protected function createWhereClause(string $condition): SQLiteWhereClause
  {
    return new SQLiteWhereClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the SQLite join-expression builder.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @param JoinType $joinType The join type to apply.
   * @return SQLiteJoinExpression Returns the SQLite join-expression builder.
   */
  protected function createJoinExpression(array|string $tableReferences, JoinType $joinType): SQLiteJoinExpression
  {
    return new SQLiteJoinExpression(query: $this->query, joinTableReferences: $tableReferences, joinType: $joinType);
  }
}
