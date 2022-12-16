<?php

namespace Assegai\Orm\Attributes\Relations;

use Assegai\Orm\Relations\RelationOptions;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToMany
{
  public function __construct(
    public readonly string $type,
    public readonly ?string $inverseSide = null,
    public readonly ?RelationOptions $options = null,
  )
  {
  }
}