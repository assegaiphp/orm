<?php

namespace Assegai\Orm\Management;

use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\ContainerException;
use Assegai\Orm\Exceptions\EmptyCriteriaException;
use Assegai\Orm\Exceptions\GeneralSQLQueryException;
use Assegai\Orm\Exceptions\IllegalTypeException;
use Assegai\Orm\Exceptions\NotFoundException;
use Assegai\Orm\Exceptions\NotImplementedException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Exceptions\SaveException;
use Assegai\Orm\Interfaces\IFactory;
use Assegai\Orm\Interfaces\QueryResultInterface;
use Assegai\Orm\Interfaces\RepositoryInterface;
use Assegai\Orm\Management\Options\FindManyOptions;
use Assegai\Orm\Management\Options\FindOneOptions;
use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Management\Options\RemoveOptions;
use Assegai\Orm\Management\Options\UpsertOptions;
use Assegai\Orm\Queries\QueryBuilder\Results\DeleteResult;
use Assegai\Orm\Queries\QueryBuilder\Results\FindResult;
use Assegai\Orm\Queries\QueryBuilder\Results\InsertResult;
use Assegai\Orm\Queries\QueryBuilder\Results\UpdateResult;
use JetBrains\PhpStorm\ArrayShape;
use ReflectionException;
use stdClass;

/**
 * Represents a repository for a specific entity.
 * @package Assegai\Orm\Management
 *
 * @template T
 * @template-implements RepositoryInterface<T>
 */
readonly class Repository implements RepositoryInterface
{
  /**
   * @param string $entityId
   * @param EntityManager $manager
   * @throws IllegalTypeException
   * @throws ReflectionException
   */
  public function __construct(
    public string        $entityId,
    public EntityManager $manager
  )
  {
    if (EntityManager::objectOrClassIsNotEntity(objectOrClass: $this->entityId))
    {
      throw new IllegalTypeException(expected: Entity::class, actual: $entityId);
    }
  }

  /**
   * @inheritDoc
   * @param array|object $targetOrEntity
   * @return QueryResultInterface
   * @throws ClassNotFoundException
   * @throws EmptyCriteriaException
   * @throws GeneralSQLQueryException
   * @throws IllegalTypeException
   * @throws ORMException
   * @throws ReflectionException
   * @throws SaveException
   */
  public function save(array|object $targetOrEntity): QueryResultInterface
  {
    return $this->manager->save(targetOrEntity: $targetOrEntity);
  }

  /**
   * @inheritDoc
   * @param stdClass|array|null $plainObjectOrObjects
   * @return object
   * @throws ClassNotFoundException
   * @throws ORMException|ReflectionException
   */
  public function create(null|object|array $plainObjectOrObjects = null): object|array
  {
    return $this->manager->create(entityClass: $this->entityId, entityLike: $plainObjectOrObjects);
  }

  /**
   * @inheritDoc
   */
  public function merge(...$entities): stdClass
  {
    return call_user_func_array([$this->manager, 'merge'], [$this->entityId, ...$entities]);
  }

  /**
   * @param object $entityLike
   * @return Entity|null
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function preload(object $entityLike): ?object
  {
    return $this->manager->preload(entityClass: $this->entityId, entityLike: $entityLike);
  }

  /**
   * @inheritDoc
   * @throws ReflectionException
   */
  public function insert(array|object $entity): InsertResult
  {
    return $this->manager->insert(entityClass: $this->entityId, entity: $entity);
  }

  /**
   * @inheritDoc
   * @throws ReflectionException
   */
  public function update(string|object|array $conditions, object|array|null $entity): UpdateResult
  {
    return $this->manager->update(entityClass: $this->entityId, partialEntity: $entity, conditions: $conditions);
  }

  /**
   * @inheritDoc
   * @param array|object|null $entity
   * @return UpdateResult|InsertResult
   * @throws ClassNotFoundException
   * @throws ContainerException
   * @throws NotImplementedException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function upsert(array|object|null $entity, UpsertOptions|array $options = []): UpdateResult|InsertResult
  {
    if (empty($entity))
    {
      throw new NotFoundException($this->entityId);
    }
    $entity = $this->getEntityFromObject(entityClassName: $this->entityId, object: $entity);
    return $this->manager->upsert(entityClass: $this->entityId, entityOrEntities: $entity, options: $options);
  }

  /**
   * @inheritDoc
   * @throws ReflectionException
   */
  public function remove(array|object|null $entityOrEntities, RemoveOptions|array|null $removeOptions = null): DeleteResult
  {
    if (empty($entityOrEntities))
    {
      throw new NotFoundException($this->entityId);
    }
    $entity = $this->getEntityFromObject(entityClassName: $this->entityId, object: $entityOrEntities);
    return $this->manager->remove(entityOrEntities: $entity, removeOptions: $removeOptions);
  }

  /**
   * @inheritDoc
   * @param array|object $entityOrEntities
   * @param RemoveOptions|array|null $removeOptions
   * @return UpdateResult
   * @throws ClassNotFoundException
   * @throws ContainerException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function softRemove(array|object|null $entityOrEntities, RemoveOptions|array|null $removeOptions = null): UpdateResult
  {
    if (empty($entityOrEntities))
    {
      throw new NotFoundException($this->entityId);
    }
    $entity = $this->getEntityFromObject(entityClassName: $this->entityId, object: $entityOrEntities);
    return $this->manager->softRemove(entityOrEntities: $entity, removeOptions: $removeOptions);
  }

  /**
   * @inheritDoc
   * @throws ReflectionException
   */
  public function delete(int|array|object $conditions): DeleteResult
  {
    return $this->manager->delete(entityClass: $this->entityId, conditions: $conditions);
  }

  /**
   * @inheritDoc
   * @param int|array|object $conditions
   * @return UpdateResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function restore(int|array|object $conditions): UpdateResult
  {
    return $this->manager->restore(entityClass: $this->entityId, conditions: $conditions);
  }

  /**
   * @param FindOptions|array|null $options
   * @return int
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function count(FindOptions|array|null $options = null): int
  {
    if (is_array($options))
    {
      $options = FindOneOptions::fromArray($options);
    }
    return $this->manager->count(entityClass: $this->entityId, options: $options);
  }

  /**
   * @inheritDoc
   * @throws ClassNotFoundException if the entity class does not exist.
   * @throws GeneralSQLQueryException if the query fails.
   * @throws ORMException if the entity class is not an entity.
   * @throws ReflectionException if the entity class does not exist.
   */
  public function find(FindOptions|array|null $findOptions = new FindOptions()): FindResult
  {
    if (is_array($findOptions))
    {
      $findOptions = FindOneOptions::fromArray($findOptions);
    }
    return $this->manager->find(entityClass: $this->entityId, findOptions: $findOptions);
  }

  /**
   * @inheritDoc
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function findBy(FindWhereOptions|array $where): FindResult
  {
    if (is_array($where))
    {
      $where = FindWhereOptions::fromArray($where);
    }
    return $this->manager->findBy(entityClass: $this->entityId, where: $where);
  }

  /**
   * @param FindManyOptions|array|null $options
   * @return array
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  #[ArrayShape(['entities' => "\array|null", 'count' => "int"])]
  public function findAndCount(FindManyOptions|array|null $options = null): FindResult
  {
    if (is_array($options))
    {
      $options = FindOneOptions::fromArray($options);
    }
    return $this->manager->findAndCount(entityClass: $this->entityId, options: $options);
  }

  /**
   * @param FindWhereOptions|array $where
   * @return array
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  #[ArrayShape(['entities' => "mixed", 'count' => "int"])]
  public function findAndCountBy(FindWhereOptions|array $where): FindResult
  {
    if (is_array($where))
    {
      $where = FindWhereOptions::fromArray($where);
    }
    return $this->manager->findAndCountBy(entityClass: $this->entityId, where: $where);
  }

  /**
   * @param FindOptions|FindOneOptions|array $options If an array is passed, the whole array will be used to generate
   * the `where` clause of a FindOneOptions object. To specify the other options, you must explicitly specify
   * the `where` clause e.g. 'where' => '...'
   * @return FindResult Returns an instance of `FindResult` which contains the entity that matches the
   * given `FindOptions`.
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function findOne(FindOptions|FindOneOptions|array $options): FindResult
  {
    if (is_array($options))
    {
      $options = FindOneOptions::fromArray($options);
    }
    return $this->manager->findOne(entityClass: $this->entityId, options: $options);
  }

  /**
   * @param string $entityClassName
   * @param object|array $object $object
   * @param IFactory|null $factory
   * @return object|array
   * @throws ClassNotFoundException
   * @throws ContainerException
   * @throws ORMException
   * @throws ReflectionException
   */
  protected function getEntityFromObject(string $entityClassName, object|array $object, ?IFactory $factory = null): object|array
  {
    if (is_array($object))
    {
      $results = [];
      foreach ($object as $obj)
      {
        $results[] = $this->getEntityFromObject(entityClassName: $entityClassName, object: $obj, factory: $factory);
      }

      return $results;
    }

    return $this->manager->getEntityFromObject(
      entityClassName: $entityClassName,
      object: $object,
      factory: $factory
    );
  }
}