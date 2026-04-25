<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Enumerations\JoinType;
use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\Sql\SQLTableReference;

/**
 * MSSQL-specific FROM-clause builder.
 */
class MsSqlTableReference extends SQLTableReference
{
  /**
   * Add a LIMIT clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return MsSqlLimitClause Returns the MSSQL paging builder.
   */
  public function limit(int $limit, ?int $offset = null): MsSqlLimitClause
  {
    return $this->createLimitClause(limit: $limit, offset: $offset);
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
   * Add a WHERE clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return MsSqlWhereClause Returns the MSSQL WHERE-clause builder.
   */
  public function where(string|array|FindOptions|FindWhereOptions $condition): MsSqlWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Add a HAVING clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param string $condition The HAVING condition to append.
   * @return MsSqlHavingClause Returns the MSSQL HAVING-clause builder.
   */
  public function having(string $condition): MsSqlHavingClause
  {
    return $this->createHavingClause(condition: $condition);
  }

  /**
   * Create the MSSQL WHERE-clause builder.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return MsSqlWhereClause Returns the MSSQL WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): MsSqlWhereClause
  {
    return new MsSqlWhereClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the MSSQL HAVING-clause builder.
   *
   * @param string $condition The HAVING condition to append.
   * @return MsSqlHavingClause Returns the MSSQL HAVING-clause builder.
   */
  protected function createHavingClause(string $condition): MsSqlHavingClause
  {
    return new MsSqlHavingClause(query: $this->query, condition: $condition);
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

  /**
   * Create the MSSQL paging builder.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return MsSqlLimitClause Returns the MSSQL paging builder.
   */
  protected function createLimitClause(int $limit, ?int $offset = null): MsSqlLimitClause
  {
    return new MsSqlLimitClause(query: $this->query, limit: $limit, offset: $offset);
  }
}
