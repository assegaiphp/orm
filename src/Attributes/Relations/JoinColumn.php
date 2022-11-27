<?php

namespace Assegai\Orm\Attributes\Relations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JoinColumn
{
  public function __construct()
  {
  }
}