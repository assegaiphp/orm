<?php

namespace Assegai\Orm\Assegai\Console\Commands\Migration;

use Assegai\Console\Commands\Migration\MigrationDown as BaseMigrationDown;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
  name: 'migration:down',
  description: 'Rollback the migrations',
  aliases: ['m:down', 'migration:rollback', 'migrate:down']
)]
class MigrationDown extends BaseMigrationDown
{
}
