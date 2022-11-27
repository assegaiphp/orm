<?php

namespace Assegai\Orm\Attributes\Relations;

use Assegai\Orm\Relations\RelationsOptions;
use Attribute;

/**
 * A one-to-many is a relation where A contains multiple instances of B, but B contains only one instance of A.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToMany
{
  /**
   * @param string $type
   * @param string|null $name
   * @param string|null $alias
   * @param RelationsOptions|null $options
   */
  public function __construct(
    public readonly string $type,
    public readonly ?string $name = null,
    public readonly ?string $alias = null,
    public ?RelationsOptions $options = null
  )
  {
    if (is_null($this->options))
    {
      $this->options = new RelationsOptions();
    }
  }
}