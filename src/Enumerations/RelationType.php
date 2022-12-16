<?php

namespace Assegai\Orm\Enumerations;

enum RelationType: string
{
  case ONE_TO_ONE = 'one-to-one';
  case ONE_TO_MANY = 'one-to-many';
  case MANY_TO_ONE = 'many-to-one';
  case MANY_TO_MANY = 'many-to-many';
}
