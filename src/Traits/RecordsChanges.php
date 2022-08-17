<?php

namespace Assegai\Orm\Traits;

use Assegai\Orm\Attributes\Columns\CreateDateColumn;
use Assegai\Orm\Attributes\Columns\DeleteDateColumn;
use Assegai\Orm\Attributes\Columns\UpdateDateColumn;
use DateTime;

trait RecordsChanges
{
  #[CreateDateColumn]
  public ?DateTime $createdAt = null;

  #[UpdateDateColumn]
  public ?DateTime $updatedAt = null;

  #[DeleteDateColumn]
  public ?DateTime $deletedAt = null;
}