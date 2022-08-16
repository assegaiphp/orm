<?php

namespace Assegai\Orm\Traits;

use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;

trait Identifiable
{
  #[PrimaryGeneratedColumn]
  public int $id = 0;
}