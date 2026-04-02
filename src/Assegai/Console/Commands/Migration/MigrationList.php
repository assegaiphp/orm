<?php

namespace Assegai\Orm\Assegai\Console\Commands\Migration;

use Assegai\Console\Commands\Migration\MigrationList as BaseMigrationList;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
  name: 'migration:list',
  description: 'List all migrations',
  aliases: ['m:list', 'migrations']
)]
class MigrationList extends BaseMigrationList
{
}
