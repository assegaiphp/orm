<?php

namespace Assegai\Orm\Interfaces;

use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\IllegalTypeException;
use Assegai\Orm\Exceptions\ORMException;
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
use stdClass as Entity;

/**
 * Interface RepositoryInterface
 * @package Assegai\Orm\Interfaces
 *
 * @template T
 */
interface RepositoryInterface
{
  /**
   * Saves a given entity or array of entities.
   *
   * If the entity already exists in the database, it is updated. If the
   * entity does not exist in the database, it is inserted. It saves all
   * given entities in a single transaction. Also supports partial updating since all
   * undefined properties are skipped.
   *
   * @param object|array<object> $targetOrEntity The target entity/entities to be saved.
   * @return QueryResultInterface Returns a QueryResultInterface instance. Use it to check if the operation was successful.
   * @throws IllegalTypeException
   */
  public function save(object|array $targetOrEntity): QueryResultInterface;

  /**
   * Creates a new entity instance or instances. Optionally accepts an object literal with entity
   * properties which will be written into newly created Entity object.
   *
   * @param object|array|null $plainObjectOrObjects an object or array literal with entity properties
   * @return object|array<object> Returns a newly created Entity object
   * @throws ClassNotFoundException
   */
  public function create(null|object|array $plainObjectOrObjects = null): object|array;

  /**
   * Merges multiple entities into a single entity.
   *
   * @param object[] ...$entities
   * @return object Returns a single entity
   * @throws ClassNotFoundException
   */
  public function merge(...$entities): object;

  /**
   * Creates a new entity from the given plain php object. If the entity already exist in the database, then
   * it loads it (and everything related to it), replaces all values with the new ones from the given object
   * and returns this new entity. This new entity is actually a loaded from the db entity with all properties
   * replaced from the new object.
   *
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
   * @param array|object $entity
   * @return InsertResult
   * @throws ORMException
   */
  public function insert(array|object $entity): InsertResult;

  /**
   * Updates entity partially. Entity can be found by a given condition(s).
   * Unlike save method executes a primitive operation without cascades, relations and other operations included.
   * Executes fast and efficient UPDATE query.
   * Does not check if entity exist in the database.
   * Condition(s) cannot be empty.
   *
   * @param string|object|array $conditions
   * @param object|array|null $entity
   * @return UpdateResult
   * @throws ORMException
   */
  public function update(string|object|array $conditions, object|array|null $entity): UpdateResult;

  /**
   * Inserts or updates a given entity or entities.
   *
   * @param object|object[] $entity The entity or entities to upsert.
   * @param UpsertOptions|array $options The options to use when upserting.
   * @return InsertResult|UpdateResult Returns the inserted or updated entity or entities.
   */
  public function upsert(object|array|null $entity, UpsertOptions|array $options = []): InsertResult|UpdateResult;

  /**
   * Removes a given entity from the database.
   *
   * @param object|object[]|null $entityOrEntities The entity or entities to remove. If an array is given, all entities in the array will be removed.
   * @param RemoveOptions|array|null $removeOptions The options to use when removing.
   * @return DeleteResult Returns a DeleteResult instance. Use it to check if the operation was successful.
   * @throws ORMException If the given entity is not an object or an array.
   */
  public function remove(object|array|null $entityOrEntities, RemoveOptions|array|null $removeOptions = null): DeleteResult;

  /**
   * Records the deletion date of a given entity.
   *
   * @param object|array|null $entityOrEntities The entity or entities to soft remove.
   * @param RemoveOptions|array|null $removeOptions The options to use when soft removing.
   * @return UpdateResult Returns an UpdateResult instance. Use it to check if the operation was successful.
   */
  public function softRemove(object|array|null $entityOrEntities, RemoveOptions|array|null $removeOptions = null): UpdateResult;

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
   * @return DeleteResult Returns the removed entities.
   * @throws ORMException If the given condition(s) is/are empty.
   */
  public function delete(int|array|object $conditions): DeleteResult;

  /**
   * Restores entities by a given condition(s).
   * Unlike save method executes a primitive operation without cascades, relations and other operations included.
   * Executes fast and efficient DELETE query.
   * Does not check if entity exist in the database.
   * Condition(s) cannot be empty.
   *
   * @param int|array|object $conditions The deletion conditions.
   * @return UpdateResult Returns the removed entities.
   */
  public function restore(int|array|object $conditions): UpdateResult;

  /**
   * Counts entities that match given options.
   * Useful for pagination.
   *
   * @param FindOptions|array|null $options The options to count entities.
   * @return int Returns the total number of entities that match the given options.
   */
  public function count(FindOptions|array|null $options = null): int;

  /**
   * Find entities that match the given `FindOptions`.
   *
   * @param FindOptions|array|null $findOptions The options to find entities.
   * @return FindResult<T> Returns an instance of `FindResult` which contains a list of entities that match the
   *
   */
  public function find(FindOptions|array|null $findOptions = new FindOptions()): FindResult;

  /**
   * Finds entities that match given `FindWhereOptions`.
   *
   * @param FindWhereOptions|array $where The options to find entities.
   * @return FindResult<T> Returns an instance of `FindResult` which contains a list of entities that match the
   * given `FindWhereOptions`.
   */
  public function findBy(FindWhereOptions|array $where): FindResult;

  /**
   * Finds entities that match given find options.
   * Also counts all entities that match given conditions,
   * but ignores pagination settings (from and take options).
   *
   * @param FindManyOptions|array|null $options The options to find entities.
   * @return FindResult<T> Returns an instance of `FindResult` which contains a list of entities that match the
   * given `FindManyOptions`.
   */
  public function findAndCount(FindManyOptions|array|null $options = null): FindResult;

  /**
   * Finds entities that match given WHERE conditions.
   * Also counts all entities that match given conditions,
   * but ignores pagination settings (from and take options).
   *
   * @param FindWhereOptions|array $where The options to find entities.
   * @return FindResult<T> Returns an instance of `FindResult` which contains a list of entities that match the
   * given `FindWhereOptions`.
   */
  public function findAndCountBy(FindWhereOptions|array $where): FindResult;

  /**
   * Finds first entity by a given find options.
   * If entity was not found in the database - returns null.
   *
   * @param FindOptions|FindOneOptions|array $options
   * @return FindResult<T> Returns an instance of `FindResult` which contains the entity that matches the
   * given `FindOptions`.
   */
  public function findOne(FindOptions|FindOneOptions|array $options): FindResult;
}