<?php

namespace Assegai\Orm\Assegai\Console\Commands\Database;

use Assegai\Console\Commands\Database\DatabaseSeed as BaseDatabaseSeed;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
  name: 'database:seed',
  description: 'Seed the database',
  aliases: ['db:seed']
)]
class DatabaseSeed extends BaseDatabaseSeed
{
}
