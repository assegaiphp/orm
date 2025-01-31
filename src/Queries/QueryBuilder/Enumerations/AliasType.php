<?php

namespace Assegai\Orm\Queries\QueryBuilder\Enumerations;

/**
 * Class AliasType.
 *
 * @package Assegai\Orm\Queries\QueryBuilder\Enumerations
 */
enum AliasType: string
{
  case FROM = 'from';
  case SELECT = 'select';
  case JOIN = 'join';
  case OTHER = 'other';
}
