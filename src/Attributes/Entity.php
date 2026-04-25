<?php

namespace Assegai\Orm\Attributes;

use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Util\SqlDialectHelper;
use Attribute;

/**
 * An Entity is a class that maps to a database table (or collection when using MongoDB). You can create an entity
 * by defining a new class and marking it with #[Entity()] attribute.
 *
 * Keep shared persistence metadata here. SQL-only storage knobs such as engine, schema, and withRowId remain
 * available for backward compatibility, but new code should express those through SqlEntityOptions.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Entity
{
  /**
   * @param string|null $table
   * @param string|null $orderBy
   * @param string|null $engine
   * @param string|null $dataSource
   * @param string|null $database
   * @param string|null $schema
   * @param bool|null $synchronize
   * @param bool|null $withRowId
   * @param array|null $protected
   * @param DataSourceType|null $driver
   */
  public function __construct(
    public ?string         $table = null,
    public ?string         $orderBy = null,
    public ?string         $engine = null,
    public ?string         $dataSource = null,
    public ?string         $database = null,
    public ?string         $schema = null,
    public ?bool           $synchronize = true,
    public ?bool           $withRowId = null,
    public ?array          $protected = ['password'],
    public ?DataSourceType $driver = null,
  )
  {
  }


  public function dataSourceName(): ?string
  {
    return $this->dataSource ?? $this->database;
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
