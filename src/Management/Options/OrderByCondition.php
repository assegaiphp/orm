<?php

namespace Assegai\Orm\Management\Options;

use Assegai\Orm\Enumerations\NullType;
use Assegai\Orm\Enumerations\OrderType;

/**
 * Represents an ordering condition for query results.
 *
 * @package Assegai\Orm\Management\Options
 */
readonly class OrderByCondition
{
  /**
   * Constructs an OrderByCondition instance.
   *
   * @param OrderType $order The order type (ASC or DESC).
   * @param NullType|null $nulls Specifies how to handle NULL values.
   */
  public function __construct(
    public OrderType $order,
    public ?NullType $nulls = null,
  )
  {}
}