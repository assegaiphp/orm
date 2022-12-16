<?php

namespace Assegai\Orm\Enumerations;

enum InheritancePattern: string
{
  case STI = 'STI';
  case CTI = 'CTI';
}
