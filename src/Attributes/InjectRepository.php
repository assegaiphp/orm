<?php

namespace Assegai\Orm\Attributes;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Management\Repository;
use Assegai\Core\ModuleManager;
use Attribute;
use ReflectionClass;
use ReflectionException;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class InjectRepository
{
  public readonly DataSource $dataSource;
  public readonly Repository $repository;

  /**
   * Constructs an InjectRepository attribute.
   *
   * @param string $entity The entity class name.
   * @throws ClassNotFoundException If the entity is not found.
   * @throws ORMException If the entity is not an entity.
   * @throws ReflectionException If the entity is not found.
   */
  public function __construct(public readonly string $entity)
  {
    EntityManager::validateEntityName($this->entity);

    $reflectionEntity = new ReflectionClass($this->entity);

    $reflectionAttributes = $reflectionEntity->getAttributes(Entity::class);

    if ( empty($reflectionAttributes) )
    {
      throw new ClassNotFoundException(className: Entity::class);
    }

    $reflectionAttribute = array_pop($reflectionAttributes);

    $entityAttribute = $reflectionAttribute->newInstance();

    if (! $entityAttribute instanceof Entity)
    {
      throw new ClassNotFoundException(className: Entity::class);
    }

    $moduleManager = ModuleManager::getInstance();

    $driver = $entityAttribute->driver;
    $dataSourceName = $entityAttribute->database ?? $moduleManager->getConfig('data_source');

    $dataSourceOptions = new DataSourceOptions(
      entities: [$reflectionEntity->newInstance()],
      name: $dataSourceName,
      type: $driver,
    );

    $this->dataSource = new DataSource( options: $dataSourceOptions );
    $this->repository = $this->dataSource->getRepository($this->entity);
  }
}