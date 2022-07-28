<?php

namespace Assegai\Orm\Interfaces;

interface IProvider
{
  public function get(string $className, ?IFactory $factory = null): object;
}