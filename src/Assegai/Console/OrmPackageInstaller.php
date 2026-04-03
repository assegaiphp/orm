<?php

namespace Assegai\Orm\Assegai\Console;

use Assegai\Console\Core\Packages\PackageInstallContext;
use Assegai\Console\Core\Packages\PackageInstallerInterface;
use Assegai\Console\Core\Packages\RootModuleIntegrator;
use Symfony\Component\Console\Command\Command;

class OrmPackageInstaller implements PackageInstallerInterface
{
  public function install(PackageInstallContext $context): int
  {
    return RootModuleIntegrator::importModule(
      $context->workspace,
      ['Assegai\\Orm\\Assegai\\OrmModule'],
      ['OrmModule::class'],
      $context->output,
    );
  }
}
