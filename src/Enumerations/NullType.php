<?php

namespace Assegai\Orm\Enumerations;

enum NullType: string
{
  case NULLS_FIRST = 'NULLS FIRST';
  case NULLS_LAST = 'NULLS LAST';
}