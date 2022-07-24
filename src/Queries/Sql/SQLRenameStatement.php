<?php

namespace Assegaiphp\Orm\Queries\Sql;

final class SQLRenameStatement
{
  /**
   * @param SQLQuery $query
   */
  public function __construct(
    private readonly SQLQuery $query,
  ) { }

  // public function database(string $from, string $to): SQLRenameDatabaseStatement
  // {
  //   return new SQLRenameDatabaseStatement( query: $this->query, oldDbName: $from, newDbName: $to );
  // }

  /**
   * @param string $from
   * @param string $to
   * @return SQLRenameTableStatement
   */
  public function table(string $from, string $to): SQLRenameTableStatement
  {
    return new SQLRenameTableStatement( query: $this->query, oldTableName: $from, newTableName: $to );
  }
}