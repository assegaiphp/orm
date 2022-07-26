<?php

namespace Assegai\Orm\Enumerations;

enum CascadeOption: string
{
  case INSERT       = 'INSERT';
  case UPDATE       = 'UPDATE';
  case REMOVE       = 'REMOVE';
  case SOFT_REMOVE  = 'SOFT_REMOVE';
  case RECOVER      = 'RECOVER';
}
