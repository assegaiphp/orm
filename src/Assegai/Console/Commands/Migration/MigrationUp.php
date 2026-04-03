<?php

namespace Assegai\Orm\Assegai\Console\Commands\Migration;

use Assegai\Console\Commands\Migration\MigrationUp as BaseMigrationUp;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
  name: 'migration:up',
  description: 'Run the migrations',
  aliases: ['m:up', 'migration:run', 'migrate:up']
)]
class MigrationUp extends BaseMigrationUp
{
}
