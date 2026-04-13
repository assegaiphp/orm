<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Enumerations\JoinType;
use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\Sql\SQLTableReference;

/**
 * MySQL-specific FROM-clause builder.
 */
class MySQLTableReference extends SQLTableReference
{
  /**
   * Add a LIMIT clause and keep the fluent chain on the MySQL builder path.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return MySQLLimitClause Returns the MySQL LIMIT-clause builder.
   */
  public function limit(int $limit, ?int $offset = null): MySQLLimitClause
  {
    return $this->createLimitClause(limit: $limit, offset: $offset);
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
   * Add a WHERE clause and keep the fluent chain on the MySQL builder path.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return MySQLWhereClause Returns the MySQL WHERE-clause builder.
   */
  public function where(string|array|FindOptions|FindWhereOptions $condition): MySQLWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Add a HAVING clause and keep the fluent chain on the MySQL builder path.
   *
   * @param string $condition The HAVING condition to append.
   * @return MySQLHavingClause Returns the MySQL HAVING-clause builder.
   */
  public function having(string $condition): MySQLHavingClause
  {
    return $this->createHavingClause(condition: $condition);
  }

  /**
   * Create the MySQL WHERE-clause builder.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return SQLWhereClause Returns the MySQL WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): MySQLWhereClause
  {
    return new MySQLWhereClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the MySQL HAVING-clause builder.
   *
   * @param string $condition The HAVING condition to append.
   * @return SQLHavingClause Returns the MySQL HAVING-clause builder.
   */
  protected function createHavingClause(string $condition): MySQLHavingClause
  {
    return new MySQLHavingClause(query: $this->query, condition: $condition);
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

  /**
   * Create the MySQL LIMIT-clause builder.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return MySQLLimitClause Returns the MySQL LIMIT-clause builder.
   */
  protected function createLimitClause(int $limit, ?int $offset = null): MySQLLimitClause
  {
    return new MySQLLimitClause(query: $this->query, limit: $limit, offset: $offset);
  }
}
