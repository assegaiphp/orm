<?php

namespace Assegai\Orm\Attributes\Relations;

use Assegai\Orm\Relations\RelationOptions;
use Attribute;

/**
 * A many-to-one is a relation where A contains multiple instances of B, but B contains only one instance of A.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToOne
{
  /**
   * @param string $type
   * @param string|null $name
   * @param string|null $alias
   * @param RelationOptions|null $options
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