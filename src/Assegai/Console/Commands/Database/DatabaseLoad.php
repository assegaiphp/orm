<?php

namespace Assegai\Orm\Assegai\Console\Commands\Database;

use Assegai\Console\Commands\Database\DatabaseLoad as BaseDatabaseLoad;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
  name: 'database:load',
  description: 'Load a schema.sql file to the database',
  aliases: ['db:load']
)]
class DatabaseLoad extends BaseDatabaseLoad
{
}
