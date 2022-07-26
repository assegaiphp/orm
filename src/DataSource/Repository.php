<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\EmptyCriteriaException;
use Assegai\Orm\Exceptions\GeneralSQLQueryException;
use Assegai\Orm\Exceptions\IllegalTypeException;
use Assegai\Orm\Exceptions\NotImplementedException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Exceptions\SaveException;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Management\FindManyOptions;
use Assegai\Orm\Management\FindOneOptions;
use Assegai\Orm\Management\FindOptions;
use Assegai\Orm\Management\FindWhereOptions;
use Assegai\Orm\Management\SaveOptions;
use Assegai\Orm\Interfaces\IRepository;
use Assegai\Orm\Queries\QueryBuilder\Results\DeleteResult;
use Assegai\Orm\Queries\QueryBuilder\Results\InsertResult;
use Assegai\Orm\Queries\QueryBuilder\Results\UpdateResult;
use JetBrains\PhpStorm\ArrayShape;
use ReflectionException;
use stdClass;

class Repository implements IRepository
{
  public readonly DataSource $dataSource;

  /**
   * @param string $entityId
   * @param EntityManager $manager
   * @throws IllegalTypeException
   * @throws ReflectionException
   */
  public function __construct(
    public readonly string $entityId,
    protected readonly EntityManager $manager
  )
  {
    if (EntityManager::isNotEntity(className: $this->entityId))
    {
      throw new IllegalTypeException(expected: Entity::class, actual: $entityId);
    }
  }

  /**
   * @inheritDoc
   * @param array|object $targetOrEntity
   * @return array
   * @throws ClassNotFoundException
   * @throws EmptyCriteriaException
   * @throws GeneralSQLQueryException
   * @throws IllegalTypeException
   * @throws ORMException
   * @throws ReflectionException
   * @throws SaveException
   */
  public function save(array|object $targetOrEntity): object|array
  {
    return $this->manager->save(targetOrEntity: $targetOrEntity);
  }

  /**
   * @inheritDoc
   * @param stdClass|array|null $plainObjectOrObjects
   * @return object
   * @throws ClassNotFoundException
   * @throws ORMException
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
   * @throws IllegalTypeException
   * @throws ORMException
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
   */
  public function update(string|object|array $conditions, stdClass|array|Entity $entity): UpdateResult
  {
    return $this->manager->update(entityClass: $this->entityId, partialEntity: $entity, conditions: $conditions);
  }

  /**
   * @inheritDoc
   * @throws NotImplementedException
   */
  public function upsert(array|object $entity): UpdateResult|InsertResult
  {
    return $this->manager->upsert(entityClass: $this->entityId, entityOrEntities: $entity);
  }

  /**
   * @inheritDoc
   */
  public function remove(array|object $entityOrEntities, ?SaveOptions $removeOptions = null): DeleteResult
  {
    return $this->manager->remove(entityOrEntities: $entityOrEntities, removeOptions: $removeOptions);
  }

  /**
   * @inheritDoc
   * @param Entity|array|stdClass $entityOrEntities
   * @param SaveOptions|null $removeOptions
   * @return UpdateResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function softRemove(array|object $entityOrEntities, ?SaveOptions $removeOptions = null): UpdateResult
  {
    return $this->manager->softRemove(entityOrEntities: $entityOrEntities, removeOptions: $removeOptions);
  }

  /**
   * @inheritDoc
   */
  public function delete(int|array|object $conditions): DeleteResult
  {
    return $this->manager->delete(entityClass: $this->entityId, conditions: $conditions);
  }

  /**
   * @inheritDoc
   * @param int|array|stdClass $conditions
   * @return UpdateResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function restore(int|array|object $conditions): UpdateResult
  {
    return $this->manager->restore(entityClass: $this->entityId, conditions: $conditions);
  }

  /**
   * @param FindOptions|null $options
   * @return int
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function count(?FindOptions $options = null): int
  {
    return $this->manager->count(entityClass: $this->entityId, options: $options);
  }

  /**
   * @param FindOptions|null $findOptions
   * @return array|null
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function find(?FindOptions $findOptions = new FindOptions()): ?array
  {
    return $this->manager->find(entityClass: $this->entityId, findOptions: $findOptions);
  }

  /**
   * @param FindWhereOptions $where
   * @return array|null
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function findBy(FindWhereOptions $where): ?array
  {
    return $this->manager->findBy(entityClass: $this->entityId, where: $where);
  }

  /**
   * @param FindManyOptions|null $options
   * @return array
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  #[ArrayShape(['entities' => "\array|null", 'count' => "int"])]
  public function findAndCount(?FindManyOptions $options = null): array
  {
    return $this->manager->findAndCount(entityClass: $this->entityId, options: $options);
  }

  /**
   * @param FindWhereOptions|array $where
   * @return array
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  #[ArrayShape(['entities' => "mixed", 'count' => "int"])]
  public function findAndCountBy(FindWhereOptions|array $where): array
  {
    return $this->manager->findAndCountBy(entityClass: $this->entityId, where: $where);
  }

  /**
   * @param FindOptions|FindOneOptions $options
   * @return Entity|null
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function findOne(FindOptions|FindOneOptions $options): ?object
  {
    return $this->manager->findOne(entityClass: $this->entityId, options: $options);
  }
}