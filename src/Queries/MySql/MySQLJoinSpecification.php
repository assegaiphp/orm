<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Enumerations\JoinType;
use Assegai\Orm\Queries\Sql\SQLJoinSpecification;

/**
 * MySQL-specific JOIN-specification builder.
 */
class MySQLJoinSpecification extends SQLJoinSpecification
{
  /**
   * Add a WHERE clause and keep the fluent chain on the MySQL builder path.
   *
   * @param string $condition The WHERE condition to append.
   * @return MySQLWhereClause Returns the MySQL WHERE-clause builder.
   */
  public function where(string $condition): MySQLWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Add a JOIN clause and keep the fluent chain on the MySQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MySQLJoinExpression Returns the MySQL join-expression builder.
   */
  public function join(array|string $tableReferences): MySQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::JOIN);
  }

  /**
   * Add a LEFT JOIN clause and keep the fluent chain on the MySQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MySQLJoinExpression Returns the MySQL join-expression builder.
   */
  public function leftJoin(array|string $tableReferences): MySQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::LEFT_JOIN);
  }

  /**
   * Add a RIGHT JOIN clause and keep the fluent chain on the MySQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MySQLJoinExpression Returns the MySQL join-expression builder.
   */
  public function rightJoin(array|string $tableReferences): MySQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::RIGHT_JOIN);
  }

  /**
   * Add an INNER JOIN clause and keep the fluent chain on the MySQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MySQLJoinExpression Returns the MySQL join-expression builder.
   */
  public function innerJoin(array|string $tableReferences): MySQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::INNER_JOIN);
  }

  /**
   * Add an OUTER JOIN clause and keep the fluent chain on the MySQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MySQLJoinExpression Returns the MySQL join-expression builder.
   */
  public function outerJoin(array|string $tableReferences): MySQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::OUTER_JOIN);
  }

  /**
   * Create the MySQL WHERE-clause builder.
   *
   * @param string $condition The WHERE condition to append.
   * @return MySQLWhereClause Returns the MySQL WHERE-clause builder.
   */
  protected function createWhereClause(string $condition): MySQLWhereClause
  {
    return new MySQLWhereClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the MySQL join-expression builder.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @param JoinType $joinType The join type to apply.
   * @return MySQLJoinExpression Returns the MySQL join-expression builder.
   */
  protected function createJoinExpression(array|string $tableReferences, JoinType $joinType): MySQLJoinExpression
  {
    return new MySQLJoinExpression(query: $this->query, joinTableReferences: $tableReferences, joinType: $joinType);
  }
}
