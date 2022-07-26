<?php

namespace Assegai\Orm\Management;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\EmptyCriteriaException;
use Assegai\Orm\Exceptions\GeneralSQLQueryException;
use Assegai\Orm\Exceptions\IllegalTypeException;
use Assegai\Orm\Exceptions\NotImplementedException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Exceptions\SaveException;
use Assegai\Orm\Exceptions\NotFoundException;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\QueryBuilder\Results\DeleteResult;
use Assegai\Orm\Queries\QueryBuilder\Results\InsertResult;
use Assegai\Orm\Queries\QueryBuilder\Results\UpdateResult;
use Assegai\Orm\Util\Filter;
use JetBrains\PhpStorm\ArrayShape;
use NumberFormatter;
use PDOStatement;
use ReflectionClass;
use ReflectionException;
use stdClass;

class EntityManager
{
  /**
   * Once created and then reused by repositories.
   */
  protected array $repositories = [];

  protected array $readonlyColumns = ['id', 'createdAt', 'updatedAt', 'deletedAt'];

  /**
   * @param DataSource $connection
   * @param SQLQuery|null $query
   * @param EntityInspector|null $inspector
   */
  public function __construct(
    protected DataSource $connection,
    protected ?SQLQuery $query = null,
    protected ?EntityInspector $inspector = null,
  )
  {
    $this->query = $query ?? new SQLQuery(db: $connection->db);
    if (!$this->inspector)
    {
      $this->inspector = EntityInspector::getInstance();
    }
  }

  public function lastInsertId(): ?int
  {
    return $this->query->lastInsertId();
  }

  /**
   * Executes a raw SQL query and returns the raw database results.
   *
   * @param string $query
   * @param array $parameters
   *
   * @return PDOStatement|false Returns a PDOStatement object, or FALSE on failure.
   * @link https://php.net/manual/en/pdo.query.php
   */
  public function query(string $query, array $parameters = []): PDOStatement|false
  {
    // TODO: Add support for all raw query parameters e.g. mode, ...fetch_mode_args
    return $this->connection->db->query($query);
  }

  /**
   * Saves all given entities in the database.
   * If entities do not exist in the database then inserts, otherwise updates.
   *
   * @param object|object[] $targetOrEntity
   * @return object|array
   * @throws ClassNotFoundException
   * @throws EmptyCriteriaException
   * @throws GeneralSQLQueryException
   * @throws IllegalTypeException
   * @throws ORMException
   * @throws ReflectionException
   * @throws SaveException
   */
  public function save(object|array $targetOrEntity): object|array
  {
    $results = [];

    /** @var object $targetOrEntity */
    if (is_object($targetOrEntity))
    {
      if (empty($targetOrEntity->id))
      {
        $saveResult = $this->insert(entityClass: $targetOrEntity::class, entity: $targetOrEntity);
      }
      else if ($entity = $this->findBy($targetOrEntity::class, new FindWhereOptions(conditions: ['id' => $targetOrEntity->id])))
      {
        $saveResult = $this->update(entityClass: $targetOrEntity::class, partialEntity: $targetOrEntity, conditions: ['id' => $targetOrEntity->id]);
      }
      else
      {
        throw new NotFoundException($targetOrEntity->id);
      }

      if ($saveResult instanceof InsertResult)
      {
        return $saveResult->generatedMaps;
      }

      if ($saveResult instanceof UpdateResult)
      {
        return $saveResult->generatedMaps;
      }

      if ($saveResult->affected === 0)
      {
        return $results;
      }

      return $this->findBy($targetOrEntity::class, new FindWhereOptions(conditions: ['id' => $this->query->lastInsertId()]));
    }
    else
    {
      foreach ($targetOrEntity as $entity)
      {
        $results[] = $this->save(targetOrEntity: $entity);
      }
    }

    return $results;
  }

  /**
   * Validates the given entity name. If an invalid entity name is given, then a
   * `ClassNotFoundException` is thrown.
   *
   * @param string $entityClass
   * @throws ClassNotFoundException
   * @throws ORMException
   */
  public static function validateEntityName(string $entityClass): void
  {
    EntityInspector::validateEntityName(entityClass: $entityClass);
  }

  /**
   * Creates a new entity instance or instances. Optionally accepts an object literal with entity
   * properties which will be written into newly created Entity object.
   *
   * @param string $entityClass
   * @param object|array|null $entityLike An entity like object or array literal with entity properties
   *
   * @return object Returns a newly created Entity object
   * @throws ClassNotFoundException
   * @throws ORMException
   */
  public function create(string $entityClass, null|object|array $entityLike = null): object
  {
    $this->validateEntityName(entityClass: $entityClass);

    $entity = new $entityClass;

    if (!empty($entityLike))
    {
      foreach ($entityLike as $key => $value)
      {
        if (property_exists($entity, $key))
        {
          $entity->$key = $value;
        }
      }
    }

    return $entity;
  }

  /**
   * Merges multiple entities into a single entity.
   *
   * @param string $entityClass
   * @param mixed ...$entities
   *
   * @return Entity Returns a single entity
   * @throws ClassNotFoundException
   * @throws ORMException
   */
  public function merge(string $entityClass, ...$entities): object
  {
    $this->validateEntityName(entityClass: $entityClass);

    $entity = new $entityClass;

    if ($entity instanceof Entity)
    {
      $object = (object) $entity;

      foreach ($entities as $item)
      {
        if (is_object($item) || is_array($item))
        {
          $object = (object) array_merge((array) $object, (array) $item);
        }
      }

      foreach ($object as $prop => $value)
      {
        if (property_exists($entity, $prop))
        {
          $entity->$prop = $value;
        }
      }
    }

    return $entity;
  }

  /**
   * Creates a new entity from the given plain php object. If the entity already exist in the database, then
   * it loads it (and everything related to it), replaces all values with the new ones from the given object
   * and returns this new entity. This new entity is actually a loaded from the db entity with all properties
   * replaced from the new object.
   * @param string $entityClass
   * @param object $entityLike
   * @return Entity|null
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function preload(string $entityClass, object $entityLike): ?object
  {
    $entity = $this->find(entityClass: $entityClass);

    if (empty($entity))
    {
      $entity = $this->create(entityClass: $entityClass, entityLike: $entityLike);
    }

    return $this->merge($entityClass, $entity, $entityLike);
  }

  /**
   * Inserts a given entity into the database.
   * Unlike the save method executes a primitive operation without
   * cascades, relations and other operations included.
   * Executes a fast and efficient `INSERT` query.
   * Does not check if the entity exist in the database, so the query will fail if
   * duplicate entity is being inserted.
   * You can execute bulk inserts using this method.
   *
   * @param string $entityClass
   * @param array|object $entity
   * @return InsertResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws IllegalTypeException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function insert(
    string $entityClass,
    array|object $entity
  ): InsertResult
  {
    $instance = $this->create(entityClass: $entityClass, entityLike: (object)$entity);

    $columns = $this->inspector->getColumns(entity: $instance, exclude: $this->readonlyColumns);
    $values = $this->inspector->getValues(entity: $instance, exclude: $this->readonlyColumns);

    $result =
      $this
        ->query
        ->insertInto(tableName: $this->inspector->getTableName(entity: $instance))
        ->singleRow(columns: $columns)
        ->values(valuesList: $values)
        ->execute();

    if ($result->isError())
    {
      throw new GeneralSQLQueryException($this->query);
    }

    $generatedMaps = new stdClass();
    foreach ($result->value() as $key => $value)
    {
      $generatedMaps->$key = $value;
    }
    return new InsertResult(identifiers: $entity, raw: $this->query->queryString(), generatedMaps: $generatedMaps);
  }

  /**
   * Updates entity partially. Entity can be found by a given condition(s).
   * Unlike save method executes a primitive operation without cascades, relations and other operations included.
   * Executes fast and efficient UPDATE query.
   * Does not check if entity exist in the database.
   * Condition(s) cannot be empty.
   *
   * @param string $entityClass
   * @param object|array $partialEntity
   * @param string|object|array $conditions
   * @return UpdateResult
   * @throws ClassNotFoundException
   * @throws EmptyCriteriaException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function update(
    string $entityClass,
    object|array $partialEntity,
    string|object|array $conditions
  ): UpdateResult
  {
    $this->validateConditions(conditions: $conditions, methodName: __METHOD__);
    $conditionString = '';

    if (empty($conditions))
    {
      throw new ORMException("Empty criteria(s) are not allowed for the update method.");
    }

    if (!is_string($conditions))
    {
      foreach ($conditions as $key => $value)
      {
        $conditionString .= "$key=" . (is_numeric($value) ? $value : "'$value'");
      }
    }
    else
    {
      $conditionString = $conditions;
    }

    if (is_array($partialEntity))
    {
      $raw = '';
      $affected = 0;
      $generatedMaps = new stdClass();

      foreach ($partialEntity as $partialItem)
      {
        $result = $this->update(entityClass: $entityClass, partialEntity: $partialItem, conditions: $conditions);
        $raw .= $result->raw . PHP_EOL;
        $affected += $result->affected;
        $generatedMaps = $result->generatedMaps;
      }

      return new UpdateResult(
        raw: $this->query->queryString(),
        affected: $this->query->rowCount(),
        identifiers: $partialEntity,
        generatedMaps: $generatedMaps
      );
    }

    $instance = $this->create(entityClass: $entityClass, entityLike: $partialEntity);
    $assignmentList = [];

    foreach ($partialEntity as $prop => $value)
    {
      if (in_array($prop, $this->inspector->getColumns(entity: $instance, exclude: $this->readonlyColumns)))
      {
        $assignmentList[$prop] = $value;
      }
    }

    $result =
      $this
        ->query
        ->update(tableName: $this->inspector->getTableName(entity: $instance))
        ->set(assignmentList: $assignmentList)
        ->where(condition: $conditionString)
        ->execute();

    if ($result->isError())
    {
      throw new GeneralSQLQueryException($this->query);
    }

    $generatedMaps = new stdClass();
    foreach ($result->value() as $key => $value)
    {
      $generatedMaps->$key = $value;
    }

    return new UpdateResult(
      raw: $this->query->queryString(),
      affected: $this->query->rowCount(),
      identifiers: $partialEntity,
      generatedMaps: $generatedMaps
    );
  }

  /**
   * 
   * @param string $entityClass
   * @param object|object[] $entityOrEntities
   * @return InsertResult|UpdateResult
   * @throws NotImplementedException
   */
  public function upsert(string $entityClass, object|array $entityOrEntities): InsertResult|UpdateResult
  {
    // TODO: #83 Implement EntityManager::upsert @amasiye
    throw new NotImplementedException("EntityManager::upsert()");
  }

  /**
   * Removes a given entity from the database.
   *
   * @param object|object[] $entityOrEntities
   * @param SaveOptions|null $removeOptions
   * @return DeleteResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function remove(
    object|array $entityOrEntities,
    ?SaveOptions $removeOptions = null
  ): DeleteResult
  {
    if (is_object($entityOrEntities))
    {
      $id = $entityOrEntities->id ?? 0;
      $statement =
        $this
          ->query
          ->deleteFrom(tableName: $this->inspector->getTableName(entity: $entityOrEntities))
          ->where("id=$id");

      $result = $statement->execute();

      if ($result->isError())
      {
        throw new GeneralSQLQueryException($this->query);
      }

      return new DeleteResult(raw: $this->query->queryString(), affected: 1);
    }

    $affected = 0;
    $raw = '';
    foreach ($entityOrEntities as $entity)
    {
      $removeResult = $this->softRemove(entityOrEntities: $entity, removeOptions: $removeOptions);
      $affected += $removeResult->affected;
      $raw .= $removeResult->raw . PHP_EOL;
    }

    return new DeleteResult(raw: $raw, affected: $affected);
  }

  /**
   * Records the deletion date of a given entity.
   *
   * @param object|object[] $entityOrEntities
   * @param SaveOptions|null $removeOptions
   * @return UpdateResult Returns the removed entities.
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function softRemove(
    object|array $entityOrEntities,
    ?SaveOptions $removeOptions = null
  ): UpdateResult
  {
    $result = null;
    $deletedAt = date(DATE_ATOM);

    if (is_object($entityOrEntities))
    {
      $statement =
        $this
          ->query
          ->update(tableName: $this->inspector->getTableName(entity: $entityOrEntities))
          ->set([Filter::getDeleteDateColumnName(entity: $entityOrEntities) => $deletedAt])
          ->where("id=$entityOrEntities->id");

      $result = $statement->execute();

      if ($result->isError())
      {
        throw new GeneralSQLQueryException($this->query);
      }

      // TODO: #88 Verify that delete occurred @amasiye
      $generatedMaps = new stdClass();
      foreach ($result->value() as $key => $value)
      {
        $generatedMaps->$key = $value;
      }

      return new UpdateResult(
        raw: $result,
        affected: $this->query->rowCount(),
        identifiers: (object) $entityOrEntities,
        generatedMaps: $generatedMaps
      );
    }

    $identifiers = new stdClass();
    $generatedMaps = new stdClass();

    $numberFormatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
    foreach ($entityOrEntities as $id => $entity)
    {
      $key = is_numeric($id) ? $numberFormatter->format($id) : $id;
      $result = $this->softRemove(entityOrEntities: $entity, removeOptions: $removeOptions);
      $identifiers->$key = $result;
      $generatedMaps->$key = $result->generatedMaps;
    }

    return new UpdateResult(
      raw: $result,
      affected: $this->query->rowCount(),
      identifiers: $identifiers,
      generatedMaps: $generatedMaps
    );
  }

  /**
   * Deletes entities by a given condition(s).
   *
   * Unlike the save method, it executes a primitive operation without cascades,
   * relations and other operations included.
   * Executes a fast and efficient `DELETE` query.
   * Does not check if the entity exists in the database.
   * Condition(s) cannot be empty.
   *
   * @param string $entityClass
   * @param int|array|object $conditions The deletion conditions.
   *
   * @return DeleteResult Returns the removed entities.
   * @throws ClassNotFoundException
   * @throws EmptyCriteriaException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function delete(
    string $entityClass,
    int|array|object $conditions
  ): DeleteResult
  {
    $this->validateConditions(conditions: $conditions, methodName: __METHOD__);

    $entity = $this->create(entityClass: $entityClass);

    $statement =
      $this
        ->query
        ->deleteFrom(tableName: $this->inspector->getTableName(entity: $entity))
        ->where(condition: $this->getConditionsString(conditions: $conditions));

    $deletionResult = $statement->execute();

    if ($deletionResult->isError())
    {
      throw new GeneralSQLQueryException($this->query);
    }

    return new DeleteResult(raw: $deletionResult->value(), affected: $this->query->rowCount());
  }

  /**
   * Restores entities by a given condition(s).
   * Unlike save method executes a primitive operation without cascades, relations and other operations included.
   * Executes fast and efficient DELETE query.
   * Does not check if entity exist in the database.
   * Condition(s) cannot be empty.
   * @param string $entityClass
   * @param int|array|object $conditions
   * @return UpdateResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function restore(
    string $entityClass,
    int|array|object $conditions
  ): UpdateResult
  {
    $entity = $this->create(entityClass: $entityClass);

    $statement =
      $this
        ->query
        ->update(tableName: $this->inspector->getTableName(entity: $entity))
        ->set([Filter::getDeleteDateColumnName(entity: $entity) => NULL])
        ->where(condition: $this->getConditionsString(conditions: $conditions));

    $restoreResult = $statement->execute();

    if ($restoreResult->isError()) {
      throw new GeneralSQLQueryException($this->query);
    }

    $generatedMaps = new stdClass();

    foreach ($restoreResult->value() as $key => $value)
    {
      $generatedMaps->$key = $value;
    }

    return new UpdateResult(
      raw: $restoreResult->value(),
      affected: $this->query->rowCount(),
      identifiers: $entity,
      generatedMaps: $generatedMaps
    );
  }

  /**
   * Counts entities that match given options.
   * Useful for pagination.
   *
   * @param string $entityClass
   * @param FindOptions|null $options
   * @return int Returns the count of entities that match the given options
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function count(
    string $entityClass,
    ?FindOptions $options = null,
  ): int
  {
    $entity = $this->create(entityClass: $entityClass);

    $statement =
      $this
        ->query
        ->select()
        ->count()
        ->from(tableReferences: $this->inspector->getTableName(entity: $entity));

    if (!empty($findOptions))
    {
      $statement = $statement->where(condition: $options);
    }

    $result = $statement->execute();

    if ($result->isError())
    {
      throw new GeneralSQLQueryException($this->query);
    }

    return $result->value()[0]['COUNT(*)'] ?? 0;
  }

  /**
   * Find entities that match the given `FindOptions`.
   *
   * @param string $entityClass
   * @param null|FindOptions $findOptions
   *
   * @return array|null
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function find(string $entityClass, ?FindOptions $findOptions = new FindOptions()): ?array
  {
    $entity = $this->create(entityClass: $entityClass);
    $statement
      = $this
      ->query
      ->select()
      ->all(columns: $this->inspector->getColumns(entity: $entity, exclude: $findOptions->exclude))
      ->from(tableReferences: $this->inspector->getTableName(entity: $entity));

    if (!empty($findOptions))
    {
      $statement = $statement->where(condition: $findOptions);
    }

    $result = $statement->execute();

    if ($result->isError())
    {
      throw new GeneralSQLQueryException($this->query);
    }

    return $result->value();
  }

  /**
   * Finds entities that match given `FindWhereOptions`.
   *
   * @param string $entityClass
   * @param FindWhereOptions|array $where
   * @return null|array<object> Returns a list of entities that match the given `FindWhereOptions`.
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function findBy(string $entityClass, FindWhereOptions|array $where): ?array
  {
    $entity = $this->create(entityClass: $entityClass);
    if (is_array($where))
    {
      $where = $where['condition'] ?? '';
    }
    $statement
      = $this
      ->query
      ->select()
      ->all(columns: $this->inspector->getColumns(entity: $entity, exclude: $where->exclude))
      ->from(tableReferences: $this->inspector->getTableName(entity: $entity))
      ->where(condition: $where);

    $result = $statement->execute();

    if ($result->isError())
    {
      throw new GeneralSQLQueryException($this->query);
    }

    return $result->value();
  }

  /**
   * Finds entities that match given find options.
   * Also counts all entities that match given conditions,
   * but ignores pagination settings (from and take options).
   *
   * @param string $entityClass
   * @param FindManyOptions|null $options
   * @return array<[object,int]>
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  #[ArrayShape(['entities' => "array|null", 'count' => "int"])]
  public function findAndCount(
    string $entityClass,
    ?FindManyOptions $options = null
  ): array
  {
    $entities = $this->find(entityClass: $entityClass, findOptions: $options);

    return ['entities' => $entities, 'count' => count($entities)];
  }

  /**
   * Finds entities that match given WHERE conditions.
   * Also counts all entities that match given conditions,
   * but ignores pagination settings (from and take options).
   * @param string $entityClass
   * @param FindWhereOptions|array $where
   * @return array
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  #[ArrayShape(['entities' => "mixed", 'count' => "int"])]
  public function findAndCountBy(
    string $entityClass,
    FindWhereOptions|array $where
  ): array
  {
    $entities = $this->findBy(entityClass: $entityClass, where: $where);

    return ['entities' => $entities, 'count' => count($entities)];
  }

  /**
   * Finds first entity by a given find options.
   * If entity was not found in the database - returns null.
   *
   * @param string $entityClass
   * @param FindOptions|FindOneOptions $options
   * @return object|null
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function findOne(
    string $entityClass,
    FindOptions|FindOneOptions $options
  ): ?object
  {
    $found = $this->find(entityClass: $entityClass, findOptions: $options);

    if (empty($found[0]))
    {
      return null;
    }

    /** @var Entity $entityClass */
    return (object)$found[0];
  }

  /**
   * @param string|object|array $conditions
   * @param string $methodName
   * @throws EmptyCriteriaException
   */
  private function validateConditions(string|object|array $conditions, string $methodName): void
  {
    if (empty($conditions))
    {
      throw new EmptyCriteriaException(methodName: $methodName);
    }
  }

  /**
   * @param int|object|array $conditions
   *
   * @return string Returns an SQL condition string
   */
  private function getConditionsString(int|object|array $conditions): string
  {
    $separator = ', ';
    $conditionsString = '';

    if (empty($conditions))
    {
      return '';
    }

    if (is_int($conditions))
    {
      $conditionsString = sprintf("id=%s", $conditions);
    }
    else
    {
      foreach ($conditions as $key => $value)
      {
        $conditionsString .= sprintf("%s=%s%s", $key, (is_numeric($value) ? $value : "'$value'"), $separator);
      }
    }

    return trim($conditionsString, $separator);
  }

  /**
   * @param string|object $className The name of the class to check.
   * @return bool Returns `true` if the given class name is an Entity instance.
   * @throws ReflectionException
   */
  public static function isEntity(string|object $className): bool
  {
    $reflectionClass = new ReflectionClass($className);
    $entityAttributes = $reflectionClass->getAttributes(Entity::class);

    if ($entityAttributes)
    {
      return true;
    }

    if ($className instanceof Entity)
    {
      return true;
    }

    if (in_array(Entity::class, class_implements($className)))
    {
      return true;
    }

    return false;
  }

  /**
   * @param string|object $className
   * @return bool
   * @throws ReflectionException
   */
  public static function isNotEntity(string|object $className): bool
  {
    return ! self::isEntity(className: $className);
  }
}