<?php

namespace Assegai\Orm\Assegai;

use Assegai\Core\Attributes\Modules\Module;
use Assegai\Core\Consumers\MiddlewareConsumer;
use Assegai\Core\Injector;
use Assegai\Core\Interfaces\AssegaiModuleInterface;
use Assegai\Core\Interfaces\ConfiguresInjectorInterface;

#[Module]
class OrmModule implements AssegaiModuleInterface, ConfiguresInjectorInterface
{
  public function configure(MiddlewareConsumer $consumer): void
  {
  }

  public function configureInjector(Injector $injector): void
  {
    $injector->registerParameterResolver(new RepositoryParameterResolver());
  }
}
