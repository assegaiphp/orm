<?php

namespace Assegai\Orm\Assegai\Console\Commands\Migration;

use Assegai\Console\Commands\Migration\MigrationRefresh as BaseMigrationRefresh;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
  name: 'migration:refresh',
  description: 'Refresh the migrations',
  aliases: ['m:refresh', 'migrate:fresh']
)]
class MigrationRefresh extends BaseMigrationRefresh
{
}
