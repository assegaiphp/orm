<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Enumerations\SQLDialect;

final class SQLRenameTableStatement
{
  private string $queryString = '';

  /**
   * @param SQLQuery $query
   * @param string $oldTableName
   * @param string $newTableName
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string   $oldTableName,
    private readonly string   $newTableName,
  )
  {
    $quotedOldTableName = $this->query->quoteIdentifier($oldTableName);
    $quotedNewTableName = $this->query->quoteIdentifier($newTableName);

    $this->queryString = match ($this->query->getDialect()) {
      SQLDialect::POSTGRESQL,
      SQLDialect::SQLITE => "ALTER TABLE $quotedOldTableName RENAME TO $quotedNewTableName",
      default => "RENAME TABLE $quotedOldTableName TO $quotedNewTableName",
    };
    $this->query->setQueryString($this->queryString);
  }

  /**
   * @return SQLQueryResult
   */
  public function execute(): SQLQueryResult
  {
    return $this->query->execute();
  }
}
