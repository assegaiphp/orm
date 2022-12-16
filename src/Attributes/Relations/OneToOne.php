<?php

namespace Assegai\Orm\Attributes\Relations;

use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Relations\RelationOptions;
use Attribute;

/**
 * One-to-one is a relation where A contains only one instance of B and B
 * contains only one instance of A. For example, if we had `User` and
 * `Profile` entities, `User` can have only a single `Profile`, while a
 * single `Profile` is owned by one `User`.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToOne
{
  /**
   * @param string $type
   * @param string|null $name
   * @param string|null $alias
   * @param RelationOptions|null $options
   * @throws ClassNotFoundException
   */
  public function __construct(
    public readonly string $type,
    public readonly ?string $name = null,
    public readonly ?string $alias = null,
    public ?RelationOptions $options = null
  )
  {
    if (is_null($this->options))
    {
      $this->options = new RelationOptions();
    }

    if (!class_exists($type))
    {
      throw new ClassNotFoundException(className: $type);
    }
  }
}