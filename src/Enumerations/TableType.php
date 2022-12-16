<?php

namespace Assegai\Orm\Enumerations;

enum TableType: string
{
  case ABSTRACT = 'abstract';
  case REGULAR = 'regular';
  case VIEW = 'view';
  case JUNCTION = 'junction';
  case CLOSURE = 'closure';
  case CLOSURE_JUNCTION = 'closure-junction';
  case ENTITY_CHILD = 'entity-child';
}
