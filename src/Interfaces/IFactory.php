<?php

namespace Assegai\Orm\Interfaces;

/**
 * The base factory interface.
 */
interface IFactory
{
  /**
   * @param string $className
   * @return object
   */
  public function create(string $className): object;
}