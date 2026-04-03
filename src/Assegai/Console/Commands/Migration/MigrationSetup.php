<?php

namespace Assegai\Orm\Assegai\Console\Commands\Migration;

use Assegai\Console\Commands\Migration\MigrationSetup as BaseMigrationSetup;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
  name: 'migration:setup',
  description: 'Setup the migrations',
  aliases: ['m:setup', 'migration:init']
)]
class MigrationSetup extends BaseMigrationSetup
{
}
