<?php

namespace Assegai\Orm\Assegai\Console\Commands\Database;

use Assegai\Console\Commands\Database\DatabaseSetup as BaseDatabaseSetup;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
  name: 'database:setup',
  description: 'Setup the database',
  aliases: ['db:setup']
)]
class DatabaseSetup extends BaseDatabaseSetup
{
}
