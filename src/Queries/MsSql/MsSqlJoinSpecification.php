<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Enumerations\JoinType;
use Assegai\Orm\Queries\Sql\SQLJoinSpecification;

/**
 * MSSQL-specific JOIN-specification builder.
 */
class MsSqlJoinSpecification extends SQLJoinSpecification
{
  /**
   * Add a WHERE clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param string $condition The WHERE condition to append.
   * @return MsSqlWhereClause Returns the MSSQL WHERE-clause builder.
   */
  public function where(string $condition): MsSqlWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Add a JOIN clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MsSqlJoinExpression Returns the MSSQL join-expression builder.
   */
  public function join(array|string $tableReferences): MsSqlJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::JOIN);
  }

  /**
   * Add a LEFT JOIN clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MsSqlJoinExpression Returns the MSSQL join-expression builder.
   */
  public function leftJoin(array|string $tableReferences): MsSqlJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::LEFT_JOIN);
  }

  /**
   * Add a RIGHT JOIN clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MsSqlJoinExpression Returns the MSSQL join-expression builder.
   */
  public function rightJoin(array|string $tableReferences): MsSqlJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::RIGHT_JOIN);
  }

  /**
   * Add an INNER JOIN clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MsSqlJoinExpression Returns the MSSQL join-expression builder.
   */
  public function innerJoin(array|string $tableReferences): MsSqlJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::INNER_JOIN);
  }

  /**
   * Add an OUTER JOIN clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MsSqlJoinExpression Returns the MSSQL join-expression builder.
   */
  public function outerJoin(array|string $tableReferences): MsSqlJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::OUTER_JOIN);
  }

  /**
   * Create the MSSQL WHERE-clause builder.
   *
   * @param string $condition The WHERE condition to append.
   * @return MsSqlWhereClause Returns the MSSQL WHERE-clause builder.
   */
  protected function createWhereClause(string $condition): MsSqlWhereClause
  {
    return new MsSqlWhereClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the MSSQL join-expression builder.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @param JoinType $joinType The join type to apply.
   * @return MsSqlJoinExpression Returns the MSSQL join-expression builder.
   */
  protected function createJoinExpression(array|string $tableReferences, JoinType $joinType): MsSqlJoinExpression
  {
    return new MsSqlJoinExpression(query: $this->query, joinTableReferences: $tableReferences, joinType: $joinType);
  }
}
