<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Enumerations\JoinType;
use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\Sql\SQLTableReference;

/**
 * PostgreSQL-specific FROM-clause builder.
 */
class PostgreSQLTableReference extends SQLTableReference
{
  /**
   * Add a LIMIT clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return PostgreSQLLimitClause Returns the PostgreSQL LIMIT-clause builder.
   */
  public function limit(int $limit, ?int $offset = null): PostgreSQLLimitClause
  {
    return $this->createLimitClause(limit: $limit, offset: $offset);
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
   * Add a WHERE clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return PostgreSQLWhereClause Returns the PostgreSQL WHERE-clause builder.
   */
  public function where(string|array|FindOptions|FindWhereOptions $condition): PostgreSQLWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Add a HAVING clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param string $condition The HAVING condition to append.
   * @return PostgreSQLHavingClause Returns the PostgreSQL HAVING-clause builder.
   */
  public function having(string $condition): PostgreSQLHavingClause
  {
    return $this->createHavingClause(condition: $condition);
  }

  /**
   * Create the PostgreSQL WHERE-clause builder.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return SQLWhereClause Returns the PostgreSQL WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): PostgreSQLWhereClause
  {
    return new PostgreSQLWhereClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the PostgreSQL HAVING-clause builder.
   *
   * @param string $condition The HAVING condition to append.
   * @return SQLHavingClause Returns the PostgreSQL HAVING-clause builder.
   */
  protected function createHavingClause(string $condition): PostgreSQLHavingClause
  {
    return new PostgreSQLHavingClause(query: $this->query, condition: $condition);
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

  /**
   * Create the PostgreSQL LIMIT-clause builder.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return PostgreSQLLimitClause Returns the PostgreSQL LIMIT-clause builder.
   */
  protected function createLimitClause(int $limit, ?int $offset = null): PostgreSQLLimitClause
  {
    return new PostgreSQLLimitClause(query: $this->query, limit: $limit, offset: $offset);
  }
}
