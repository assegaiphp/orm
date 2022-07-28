<?php

namespace Assegai\Orm\Management;

use Assegai\Orm\Exceptions\ContainerException;
use Assegai\Orm\Interfaces\IEntityStoreOwner;
use Assegai\Orm\Interfaces\IFactory;
use Assegai\Orm\Interfaces\IProvider;
use ReflectionClass;
use ReflectionException;

/**
 *
 */
class EntityProvider implements IProvider
{
  /**
   * @param IEntityStoreOwner $owner
   */
  public function __construct(protected readonly IEntityStoreOwner $owner)
  {
  }

  /**
   * @throws ReflectionException|ContainerException
   */
  public function get(string $className, ?IFactory $factory = null): object
  {
    if ($this->owner->hasStoreEntry($className))
    {
      return $this->owner->getStoreEntry($className);
    }

    $reflectionClass = new ReflectionClass($className);
    $instance = null;

    if (!$reflectionClass->isInstantiable())
    {
      throw new ContainerException(storeOwner: $this->owner, message: 'Cannot instantiate ' . $className);
    }

    if ($factory)
    {
      return $factory->create($className);
    }

    return $reflectionClass->newInstance();
  }
}