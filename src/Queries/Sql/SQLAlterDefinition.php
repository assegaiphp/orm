<?php

namespace Assegaiphp\Orm\Queries\Sql;

final class SQLAlterDefinition
{
  public function __construct(
    private readonly SQLQuery $query
  )
  {
  }

  public function database(): mixed
  {
    // TODO: Implement database()
    return;
  }

  public function table(string $tableName): SQLAlterTableOption
  {
    $this->query->setQueryString(queryString: "ALTER TABLE `$tableName`");
    return new SQLAlterTableOption( query: $this->query );
  }
}
