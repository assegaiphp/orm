<?php

namespace Assegai\Orm\Assegai\Console\Commands\Migration;

use Assegai\Console\Commands\Migration\MigrationCreate as BaseMigrationCreate;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
  name: 'migration:create',
  description: 'Create a new migration',
  aliases: ['m:create', 'migration:make']
)]
class MigrationCreate extends BaseMigrationCreate
{
}
