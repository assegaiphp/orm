<?php

namespace Assegai\Orm\Lifecycle;

use Assegai\Orm\Interfaces\IFactory;

abstract class AbstractFactory implements IFactory
{
  public final function __construct()
  {
  }
}