<?php

namespace Assegai\Orm\Attributes;

use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Util\SqlDialectHelper;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class SqlEntityOptions
{
  public function __construct(
    public ?string $engine = null,
    public ?string $schema = null,
    public ?bool $withRowId = null,
  ) {
  }

  public function engineForDialect(SQLDialect|DataSourceType|null $dialect): ?string
  {
    $dialect = $this->normalizeDialect($dialect);

    if (!in_array($dialect, [SQLDialect::MYSQL, SQLDialect::MARIADB], true)) {
      return null;
    }

    return $this->engine;
  }

  public function schemaForDialect(SQLDialect|DataSourceType|null $dialect): ?string
  {
    $dialect = $this->normalizeDialect($dialect);

    if (!in_array($dialect, [SQLDialect::POSTGRESQL, SQLDialect::MSSQL], true)) {
      return null;
    }

    return $this->schema;
  }

  public function withoutRowIdForDialect(SQLDialect|DataSourceType|null $dialect): ?bool
  {
    $dialect = $this->normalizeDialect($dialect);

    if ($dialect !== SQLDialect::SQLITE || $this->withRowId === null) {
      return null;
    }

    return $this->withRowId === false;
  }

  private function normalizeDialect(SQLDialect|DataSourceType|null $dialect): ?SQLDialect
  {
    if ($dialect instanceof DataSourceType) {
      return SqlDialectHelper::fromDataSourceType($dialect);
    }

    return $dialect;
  }
}
