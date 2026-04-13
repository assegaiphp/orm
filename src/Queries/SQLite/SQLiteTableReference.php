<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Enumerations\JoinType;
use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\Sql\SQLTableReference;

/**
 * SQLite-specific FROM-clause builder.
 */
class SQLiteTableReference extends SQLTableReference
{
  /**
   * Add a LIMIT clause and keep the fluent chain on the SQLite builder path.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return SQLiteLimitClause Returns the SQLite LIMIT-clause builder.
   */
  public function limit(int $limit, ?int $offset = null): SQLiteLimitClause
  {
    return $this->createLimitClause(limit: $limit, offset: $offset);
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
   * Add a WHERE clause and keep the fluent chain on the SQLite builder path.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return SQLiteWhereClause Returns the SQLite WHERE-clause builder.
   */
  public function where(string|array|FindOptions|FindWhereOptions $condition): SQLiteWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Add a HAVING clause and keep the fluent chain on the SQLite builder path.
   *
   * @param string $condition The HAVING condition to append.
   * @return SQLiteHavingClause Returns the SQLite HAVING-clause builder.
   */
  public function having(string $condition): SQLiteHavingClause
  {
    return $this->createHavingClause(condition: $condition);
  }

  /**
   * Create the SQLite WHERE-clause builder.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return SQLWhereClause Returns the SQLite WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): SQLiteWhereClause
  {
    return new SQLiteWhereClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the SQLite HAVING-clause builder.
   *
   * @param string $condition The HAVING condition to append.
   * @return SQLHavingClause Returns the SQLite HAVING-clause builder.
   */
  protected function createHavingClause(string $condition): SQLiteHavingClause
  {
    return new SQLiteHavingClause(query: $this->query, condition: $condition);
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

  /**
   * Create the SQLite LIMIT-clause builder.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return SQLiteLimitClause Returns the SQLite LIMIT-clause builder.
   */
  protected function createLimitClause(int $limit, ?int $offset = null): SQLiteLimitClause
  {
    return new SQLiteLimitClause(query: $this->query, limit: $limit, offset: $offset);
  }
}
