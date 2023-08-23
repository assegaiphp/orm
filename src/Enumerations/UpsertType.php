<?php

namespace Assegai\Orm\Enumerations;

/**
 * The type of upsert to perform.
 */
enum UpsertType: string
{
  case UPSERT = 'upsert';
  case ON_CONFLICT_DO_UPDATE = 'on-conflict-do-update';
  case ON_DUPLICATE_KEY_UPDATE = 'on-duplicate-key-update';
}
