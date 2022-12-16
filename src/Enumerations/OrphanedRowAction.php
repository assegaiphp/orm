<?php

namespace Assegai\Orm\Enumerations;

enum OrphanedRowAction: string
{
  case NULLIFY = 'nullify';
  case DELETE = 'delete';
  case SOFT_DELETE = 'soft-delete';
  case DISABLE = 'disable';
}
