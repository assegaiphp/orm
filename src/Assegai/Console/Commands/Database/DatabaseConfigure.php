<?php

namespace Assegai\Orm\Assegai\Console\Commands\Database;

use Assegai\Console\Commands\Database\DatabaseConfigure as BaseDatabaseConfigure;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
  name: 'database:configure',
  description: 'Setup the database configuration',
  aliases: ['database:config', 'db:config']
)]
class DatabaseConfigure extends BaseDatabaseConfigure
{
}
