<?php

namespace Assegai\Orm\Attributes;

use Assegai\Orm\Enumerations\DataSourceType;
use Attribute;

/**
 * An Entity is a class that maps to a database table (or collection when using MongoDB). You can create an entity
 * by defining a new class and marking it with #[Entity()] attribute.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Entity
{
  /**
   * @param string|null $table
   * @param string|null $orderBy
   * @param string|null $engine
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
    public ?string         $database = null,
    public ?string         $schema = null,
    public ?bool           $synchronize = true,
    public ?bool           $withRowId = false,
    public ?array          $protected = ['password'],
    public ?DataSourceType $driver = DataSourceType::MYSQL,
  )
  {
  }
}