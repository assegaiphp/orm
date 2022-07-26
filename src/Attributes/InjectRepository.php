<?php

namespace Assegai\Orm\Attributes;

use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\Repository;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Management\EntityManager;
use Attribute;
use ReflectionClass;
use ReflectionException;

#[Attribute(Attribute::TARGET_PARAMETER)]
class InjectRepository
{
  public readonly DataSource $dataSource;
  public readonly Repository $repository;

  /**
   * @param string $entity
   * @throws ClassNotFoundException
   * @throws ORMException
   * @throws ReflectionException
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

    $driver = $entityAttribute->driver;
    $database = $entityAttribute->database;

    $dataSourceOptions = new DataSourceOptions(
      entities: [$reflectionEntity->newInstance()],
      database: $database,
      type: $driver,
    );

    $this->dataSource = new DataSource( options: $dataSourceOptions );
    $this->repository = $this->dataSource->getRepository($this->entity);
  }
}