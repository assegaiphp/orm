<?php

namespace Assegai\Orm\Management;

use Assegai\Orm\Enumerations\NullType;
use Assegai\Orm\Enumerations\OrderType;

class OrderByCondition
{
  public function __construct(
    public readonly OrderType $order,
    public readonly ?NullType $nulls = null,
  )
  {}
}