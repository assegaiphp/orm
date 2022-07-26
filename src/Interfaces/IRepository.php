<?php

namespace Assegai\Orm\Interfaces;

use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\IllegalTypeException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Management\FindManyOptions;
use Assegai\Orm\Management\FindOneOptions;
use Assegai\Orm\Management\FindOptions;
use Assegai\Orm\Management\FindWhereOptions;
use Assegai\Orm\Management\SaveOptions;
use Assegai\Orm\Queries\QueryBuilder\Results\DeleteResult;
use Assegai\Orm\Queries\QueryBuilder\Results\InsertResult;
use Assegai\Orm\Queries\QueryBuilder\Results\UpdateResult;

use stdClass as Entity;

interface IRepository
{
  /**
   * Saves a given entity or array of entities.
   *
   * If the entity already exists in the database, it is updated. If the
   * entity does not exist in the database, it is inserted. It saves all
   * given entities in a single transaction. Also supports partial updating since all
   * undefined properties are skipped.
   *
   * @param object|array<Entity> $targetOrEntity The target entity/entities to be saved.
   *
   * @return object|array Returns the saved entity/entities.
   * @throws IllegalTypeException
   */
  public function save(object|array $targetOrEntity): object|array;

  /**
   * Creates a new entity instance or instances. Optionally accepts an object literal with entity
   * properties which will be written into newly created Entity object.
   *
   * @param Entity|array|null $plainObjectOrObjects an object or array literal with entity properties
   *
   * @return object|array<Entity> Returns a newly created Entity object
   * @throws ClassNotFoundException
   */
  public function create(null|Entity|array $plainObjectOrObjects = null): object|array;

  /**
   * Merges multiple entities into a single entity.
   *
   * @param Entity[] ...$entities
   *
   * @return Entity Returns a single entity
   * @throws ClassNotFoundException
   */
  public function merge(...$entities): Entity;

  /**
   * Creates a new entity from the given plain php object. If the entity already exist in the database, then
   * it loads it (and everything related to it), replaces all values with the new ones from the given object
   * and returns this new entity. This new entity is actually a loaded from the db entity with all properties
   * replaced from the new object.
   * @throws ORMException
   */
  public function preload(object $entityLike): ?object;

  /**
   * Inserts a given entity into the database.
   * Unlike the save method executes a primitive operation without
   * cascades, relations and other operations included.
   * Executes a fast and efficient `INSERT` query.
   * Does not check if the entity exist in the database, so the query will fail if
   * duplicate entity is being inserted.
   * You can execute bulk inserts using this method.
   *
   * @param array|Entity $entity
   * @return InsertResult
   * @throws ORMException
   */
  public function insert(array|Entity $entity): InsertResult;

  /**
   * Updates entity partially. Entity can be found by a given condition(s).
   * Unlike save method executes a primitive operation without cascades, relations and other operations included.
   * Executes fast and efficient UPDATE query.
   * Does not check if entity exist in the database.
   * Condition(s) cannot be empty.
   *
   * @param string|Entity|array $conditions
   * @param array|Entity $entity
   * @return UpdateResult
   * @throws ORMException
   */
  public function update(string|Entity|array $conditions, Entity|array $entity): UpdateResult;

  /**
   * @param Entity|Entity[] $entity
   * @return InsertResult|UpdateResult
   */
  public function upsert(object|array $entity): InsertResult|UpdateResult;

  /**
   * Removes a given entity from the database.
   *
   * @param Entity|array $entityOrEntities
   * @param SaveOptions|null $removeOptions
   * @return DeleteResult
   * @throws ORMException
   */
  public function remove(Entity|array $entityOrEntities, ?SaveOptions $removeOptions = null): DeleteResult;

  /**
   * Records the deletion date of a given entity.
   *
   * @param object|array $entityOrEntities
   * @param SaveOptions|null $removeOptions
   * @return UpdateResult Returns the removed entities.
   */
  public function softRemove(object|array $entityOrEntities, ?SaveOptions $removeOptions = null): UpdateResult;

  /**
   * Deletes entities by a given condition(s).
   *
   * Unlike the save method, it executes a primitive operation without cascades,
   * relations and other operations included.
   * Executes a fast and efficient `DELETE` query.
   * Does not check if the entity exists in the database.
   * Condition(s) cannot be empty.
   *
   * @param int|array|object $conditions The deletion conditions.
   *
   * @return DeleteResult Returns the removed entities.
   * @throws ORMException
   */
  public function delete(int|array|object $conditions): DeleteResult;

  /**
   * Restores entities by a given condition(s).
   * Unlike save method executes a primitive operation without cascades, relations and other operations included.
   * Executes fast and efficient DELETE query.
   * Does not check if entity exist in the database.
   * Condition(s) cannot be empty.
   */
  public function restore(int|array|object $conditions): UpdateResult;

  /**
   * Counts entities that match given options.
   * Useful for pagination.
   *
   * @param FindOptions|null $options
   * @return int Returns the count of entities that match the given options
   */
  public function count(?FindOptions $options = null): int;

  /**
   * Find entities that match the given `FindOptions`.
   *
   * @param null|FindOptions $findOptions
   *
   * @return array|null
   */
  public function find(?FindOptions $findOptions = new FindOptions()): ?array;

  /**
   * Finds entities that match given `FindWhereOptions`.
   *
   * @return null|array<Entity> Returns a list of entities that match the given `FindWhereOptions`.
   */
  public function findBy(FindWhereOptions $where): ?array;

  /**
   * Finds entities that match given find options.
   * Also counts all entities that match given conditions,
   * but ignores pagination settings (from and take options).
   *
   * @return array<[Entity,int]>
   */
  public function findAndCount(?FindManyOptions $options = null): array;

  /**
   * Finds entities that match given WHERE conditions.
   * Also counts all entities that match given conditions,
   * but ignores pagination settings (from and take options).
   */
  public function findAndCountBy(FindWhereOptions $where): array;

  /**
   * Finds first entity by a given find options.
   * If entity was not found in the database - returns null.
   *
   * @param FindOptions|FindOneOptions $options
   *
   * @return null|Entity Returns the entity if found, null otherwise.
   */
  public function findOne(FindOptions|FindOneOptions $options): ?object;
}