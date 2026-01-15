<?php

namespace Assegai\Orm\Management;

use Assegai\Orm\Exceptions\ContainerException;
use Assegai\Orm\Interfaces\IEntityStoreOwner;
use Assegai\Orm\Interfaces\IFactory;
use Assegai\Orm\Interfaces\IProvider;
use ReflectionClass;
use ReflectionException;

/**
 * Class EntityProvider.
 */
class EntityProvider implements IProvider
{
  /**
   * @param IEntityStoreOwner $owner
   * @param IFactory|null $factory
   */
  public function __construct(protected readonly IEntityStoreOwner $owner, protected ?IFactory $factory = null)
  {
  }

  /**
   * @param string $className
   * @return object
   * @throws ContainerException
   * @throws ReflectionException
   */
  public function get(string $className): object
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

    if ($this->factory)
    {
      return $this->factory->create($className);
    }

    return $reflectionClass->newInstance();
  }
}