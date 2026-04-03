<?php

namespace Assegai\Orm\Assegai\Console\Commands\Migration;

use Assegai\Console\Commands\Migration\MigrationRedo as BaseMigrationRedo;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
  name: 'migration:redo',
  description: 'Redo the last migration',
  aliases: ['m:redo']
)]
class MigrationRedo extends BaseMigrationRedo
{
}
