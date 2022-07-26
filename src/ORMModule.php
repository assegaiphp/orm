<?php

namespace Assegai\Orm;

use Assegai\Core\Attributes\Module;
use Assegai\Orm\Attributes\Entity;

class ORMModule
{
  /**
   * @param array $providers
   * @param array $controllers
   * @param array $imports
   * @param array $exports
   */
  public function __construct(
    public readonly array $providers = [],
    public readonly array $controllers = [],
    public readonly array $imports = [],
    public readonly array $exports = [],
  )
  {
  }

  public static function forRoot(?OrmModuleOptions $options = null): Module
  {
    // TODO: Implement forRoot()
    return new Module();
  }

  /**
   * @param string[]|Entity[]|string|Entity $entities
   * @return Module
   */
  public static function forFeature(array $entities): Module
  {
    // TODO: Implement forFeature()
    return new Module();
  }
}