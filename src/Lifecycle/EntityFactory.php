<?php

namespace Assegai\Orm\Lifecycle;

class EntityFactory extends AbstractFactory
{
  public function create(string $className): object
  {
    return new $className();
  }
}