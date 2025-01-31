<?php

namespace Assegai\Orm\Attributes\Relations;

use Assegai\Orm\Relations\RelationOptions;
use Attribute;

/**
 * A one-to-many is a relation where A contains multiple instances of B, but B contains only one instance of A.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToMany
{
  /**
   * OneToMany constructor.
   *
   * @param string $type The type of the relation.
   * @param string|null $name The name of the relation.
   * @param string|null $alias The alias of the relation.
   * @param RelationOptions|null $options The options of the relation.
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
  }
}