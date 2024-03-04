<?php

namespace Assegai\Orm\Management;

use Assegai\Core\Config;
use Assegai\Core\ModuleManager;
use Assegai\Core\Util\Debug\Log;
use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\CreateDateColumn;
use Assegai\Orm\Attributes\Columns\DeleteDateColumn;
use Assegai\Orm\Attributes\Columns\EmailColumn;
use Assegai\Orm\Attributes\Columns\PasswordColumn;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Columns\UpdateDateColumn;
use Assegai\Orm\Attributes\Columns\URLColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\ContainerException;
use Assegai\Orm\Exceptions\EmptyCriteriaException;
use Assegai\Orm\Exceptions\GeneralSQLQueryException;
use Assegai\Orm\Exceptions\IllegalTypeException;
use Assegai\Orm\Exceptions\NotFoundException;
use Assegai\Orm\Exceptions\NotImplementedException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Exceptions\SaveException;
use Assegai\Orm\Exceptions\TypeConversionException;
use Assegai\Orm\Exceptions\ValidationException;
use Assegai\Orm\Interfaces\IEntityStoreOwner;
use Assegai\Orm\Interfaces\IFactory;
use Assegai\Orm\Interfaces\QueryResultInterface;
use Assegai\Orm\Management\Inspectors\EntityInspector;
use Assegai\Orm\Management\Options\FindManyOptions;
use Assegai\Orm\Management\Options\FindOneOptions;
use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Management\Options\RemoveOptions;
use Assegai\Orm\Management\Options\UpsertOptions;
use Assegai\Orm\Metadata\EntityMetadata;
use Assegai\Orm\Metadata\RelationPropertyMetadata;
use Assegai\Orm\Queries\QueryBuilder\Results\DeleteResult;
use Assegai\Orm\Queries\QueryBuilder\Results\FindResult;
use Assegai\Orm\Queries\QueryBuilder\Results\InsertResult;
use Assegai\Orm\Queries\QueryBuilder\Results\UpdateResult;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLQueryResult;
use Assegai\Orm\Util\Filter;
use Assegai\Orm\Util\TypeConversion\GeneralConverters;
use Assegai\Orm\Util\TypeConversion\TypeResolver;
use DateTime;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use NumberFormatter;
use PDOStatement;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;
use UnitEnum;

/**
 * Class EntityManager. The EntityManager is the central access point to ORM functionality.
 * @package Assegai\Orm\Management
 *
 * @template T of object
 */
class EntityManager implements IEntityStoreOwner
{
  const LOG_TAG = '[Entity Manager]';
  /**
   * Once created and then reused by repositories.
   */
  protected array $entities = [];

  /**
   * @var array|string[]
   */
  protected array $readonlyColumns = ['id', 'createdAt', 'updatedAt', 'deletedAt'];
  /**
   * @var array|string[]
   */
  protected array $secure = ['password'];
  /**
   * @var array
   */
  protected array $customSecure = [];

  /**
   * @var array An array of default converters.
   */
  protected array $defaultConverters = [];

  /**
   * @var array An array of custom converters.
   */
  protected array $customConverters = [];

  /**
   * Constructs a new EntityManager instance.
   *
   * @param DataSource $connection The data source to use.
   * @param SQLQuery|null $query The query to use.
   * @param EntityInspector|null $inspector The entity inspector to use.
   * @param TypeResolver|null $typeResolver The type resolver to use.
   */
  public function __construct(
    protected DataSource $connection,
    protected ?SQLQuery $query = null,
    protected ?EntityInspector $inspector = null,
    protected ?TypeResolver $typeResolver = null,
  )
  {
    $this->query = $query ?? new SQLQuery(db: $connection->getClient());

    // TODO: *BREAKING_CHANGE* Remove this binding as it breaks the inversion of control principal
    if (!$this->inspector)
    {
      $this->inspector = EntityInspector::getInstance();
    }

    // TODO: *BREAKING_CHANGE* Remove this binding as it breaks the inversion of control principal
    if (!$this->typeResolver)
    {
      $this->typeResolver = TypeResolver::getInstance();
    }

    $this->defaultConverters[] = new GeneralConverters();
    if ($customConvertors = ModuleManager::getInstance()->getConfig('convertors'))
    {
      $this->customConverters[] = $customConvertors;
    }
  }

  /**
   * @return int|null
   */
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
    return $this->connection->getClient()->query($query);
  }

  /**
   * Saves all given entities in the database.
   * If entities do not exist in the database then inserts, otherwise updates.
   *
   * @param object|object[] $targetOrEntity
   * @return QueryResultInterface
   * @throws ClassNotFoundException
   * @throws EmptyCriteriaException
   * @throws GeneralSQLQueryException
   * @throws IllegalTypeException
   * @throws ORMException
   * @throws ReflectionException
   * @throws SaveException
   */
  public function save(object|array $targetOrEntity): QueryResultInterface
  {
    $results = [];

    /** @var object $targetOrEntity */
    if (is_object($targetOrEntity))
    {
      if (empty($targetOrEntity->id))
      {
        $saveResult = $this->insert(entityClass: $targetOrEntity::class, entity: $targetOrEntity);
      }
      else if ($this->findBy($targetOrEntity::class, new FindWhereOptions(conditions: ['id' => $targetOrEntity->id])))
      {
        $saveResult = $this->update(entityClass: $targetOrEntity::class, partialEntity: $targetOrEntity, conditions: ['id' => $targetOrEntity->id]);
      }
      else
      {
        $saveResult = new SQLQueryResult([], [new NotFoundException($targetOrEntity->id)]);
      }

      if ($saveResult instanceof InsertResult || $saveResult instanceof UpdateResult || $saveResult instanceof  DeleteResult)
      {
        return $saveResult;
      }

      return $this->findBy($targetOrEntity::class, new FindWhereOptions(conditions: ['id' => $this->query->lastInsertId()]));
    }

    foreach ($targetOrEntity as $entity)
    {
      $results[] = $this->save(targetOrEntity: $entity);
    }

    return new SQLQueryResult($results);
  }

  /**
   * Asserts that the specified class name is a valid entity and throws an exception if it is not.
   *
   * @param string $entityClass The name of the class to validate.
   * @return void
   * @throws ClassNotFoundException If the class does not exist.
   * @throws ORMException If the class does not have the required attributes.
   */
  public static function validateEntityName(string $entityClass): void
  {
    EntityInspector::getInstance()->validateEntityName(entityClass: $entityClass);
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
   * @throws ORMException|ReflectionException
   */
  public function create(string $entityClass, null|object|array $entityLike = null): object
  {
    $this->validateEntityName(entityClass: $entityClass);

    $entity = new $entityClass;

    if (!empty($entityLike))
    {
      foreach ($entityLike as $entityLikePropertyName => $entityLikePropertyValue)
      {
        if (property_exists($entity, $entityLikePropertyName))
        {
          $entityLikeReflectionProperty = new ReflectionProperty($entityLike, $entityLikePropertyName);
          $entityClassReflectionProperty = new ReflectionProperty($entityClass, $entityLikePropertyName);

          if (is_null($entityLikePropertyValue))
          {
            $entityLikePropertyValue = $this->getDefaultColumnValue($entityClassReflectionProperty);

            if (!$entityClassReflectionProperty->getType()->allowsNull())
            {
              continue;
            }
          }

          $entityLikeReflectionPropertyType = $entityLikeReflectionProperty->getType()?->getName();
          $entityClassReflectionPropertyType = $entityClassReflectionProperty->getType()?->getName();
          $typesMatch = $entityLikeReflectionPropertyType === $entityClassReflectionPropertyType;

          $sourceType = $entityLikeReflectionPropertyType ?? gettype($entityLikePropertyValue);
          $targetType = $entityClassReflectionPropertyType ?? gettype($entityLikePropertyValue);

          $entity->$entityLikePropertyName =
            $typesMatch
              ? $entityLikePropertyValue
              : $this->castValue(value: $entityLikePropertyValue, sourceType: $sourceType, targetType: $targetType) ?? $entityLikePropertyValue;
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
      $object = $entity;

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
   * @throws ReflectionException
   */
  public function preload(string $entityClass, object $entityLike): ?object
  {
    $findOptions = [];

    foreach ($entityLike as $prop => $value)
    {
      if (property_exists($entityClass, $prop))
      {
        $findOptions[$prop] = $value;
      }
    }

    $entity = $this->find(entityClass: $entityClass, findOptions: FindOptions::fromArray($findOptions));

    if (empty($entity->getData()))
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
    # Check if the entity matches the given entity class
    if (! $this->inspector->hasValidEntityStructure(entity: $entity, entityClass: $entityClass))
    {
      return new InsertResult(
        identifiers: $entity,
        raw: $this->query->queryString(),
        generatedMaps: null,
        errors: [new ORMException("Entity does not match the given entity class.")]
      );
    }

    $instance = $this->create(entityClass: $entityClass, entityLike: (object)$entity);
    $columnsMeta = [];

    $columns = $this->inspector->getColumns(entity: $instance, meta: $columnsMeta);
    $values = $this->inspector->getValues(entity: $instance, options: [
      'filter' => true,
      'columnTypes' => $columnsMeta['columnTypes'] ?? []
    ]);

    $result =
      $this
        ->query
        ->insertInto(tableName: $this->inspector->getTableName(entity: $instance))
        ->singleRow(columns: $columns)
        ->values(valuesList: $values)
        ->execute();

    if ($result->isError())
    {
      http_response_code(500);
      $error = new GeneralSQLQueryException($this->query);
      Log::error(self::LOG_TAG, $error);

      return new InsertResult(
        identifiers: $entity,
        raw: $this->query->queryString(),
        generatedMaps: $result,
        errors: [$error]
      );
    }

    # Find the record by the last insert id and hydrate the entity
    $result = $this->findOne(entityClass: $entityClass, options: new FindOneOptions(where: ['id' => $this->lastInsertId()]));

    if ($result->isError())
    {
      http_response_code(500);
      $error = new GeneralSQLQueryException($this->query);
      Log::error(self::LOG_TAG, $error);

      return new InsertResult(
        identifiers: $entity,
        raw: $this->query->queryString(),
        generatedMaps: $result,
        errors: [$result->getErrors()]
      );
    }

    $entity = is_array($result->getData())
      ? $result->getData()[0]
      : $result->getData();

    $generatedMaps = (object)array_merge((array)$entity, (array)new stdClass());
    $generatedMaps->id = $this->lastInsertId();

    foreach ($generatedMaps as $prop => $value)
    {
      if (in_array($prop, $this->getSecure()))
      {
        unset($generatedMaps->$prop);
      }
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
   * @throws ReflectionException
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
        $conditionString .= "$key=" . match (true) {
          is_numeric($value) => $value,
          $value instanceof UnitEnum && property_exists($value, 'value') => $value->value,
          default => "'$value'"
        };
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
        $generatedMaps = $result->generatedMaps;
      }

      return new UpdateResult(
        raw: $this->query->queryString(),
        affected: $this->query->rowCount(),
        identifiers: (object)$partialEntity,
        generatedMaps: $generatedMaps
      );
    }

    $instance = $this->create(entityClass: $entityClass, entityLike: $partialEntity);
    $assignmentList = [];
    $columnOptions = [];
    $columnMap = $this->inspector->getColumns(entity: $instance, exclude: $this->readonlyColumns, meta: $columnOptions);

    foreach ($partialEntity as $prop => $value)
    {
      # Get the correct prop name
      $columnName = $this->getColumnNameFromProperty($instance, $prop);

      if ($this->mapContainsColumnName($columnMap, $columnName))
      {
        if (!is_null($value))
        {
          if ($value instanceof UnitEnum && property_exists($value, 'value'))
          {
            $value = $value->value;
          }

          if ($value instanceof DateTime)
          {
            $value = $this->inspector->convertDateTimeToString($value, $prop, $columnOptions);
          }
          $assignmentList[$columnName] = $value;
        }
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

    $updatedEntity = $this->findOne(entityClass: $entityClass, options: new FindOptions(where: $conditions));
    $generatedMaps = $updatedEntity->getData() ?? new stdClass();

    return new UpdateResult(
      raw: $this->query->queryString(),
      affected: $this->query->rowCount(),
      identifiers: $partialEntity,
      generatedMaps: $generatedMaps
    );
  }

  /**
   * Inserts a new entity or array of entities unless they already exist in the database, if they do then updates.
   *
   * @param string $entityClass The entity class name.
   * @param object|object[] $entityOrEntities The entity or entities to upsert.
   * @return InsertResult|UpdateResult Returns an InsertResult or UpdateResult.
   * @throws ClassNotFoundException
   * @throws NotImplementedException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function upsert(
    string $entityClass,
    object|array $entityOrEntities,
    array|UpsertOptions $options
  ): InsertResult|UpdateResult
  {
    // TODO: #83 Implement EntityManager::upsert @amasiye
    if (is_array($entityOrEntities))
    {
      $results = [];
      $errors = [];
      foreach ($entityOrEntities as $entity)
      {
        $result = $this->upsert(entityClass: $entityClass, entityOrEntities: $entity, options: $options);

        if ($result->isError())
        {
          $errors[] = $result->getErrors();
        }

        $results[] = $result->getData();
      }

      $generatedMaps = new stdClass();
      $generatedMaps->results = $results;

      return new UpdateResult(
        raw: $this->query->queryString(),
        affected: $this->query->rowCount(),
        identifiers: $entityOrEntities,
        generatedMaps: $generatedMaps,
        errors: $errors
      );
    }

    $this->validateEntityName(entityClass: $entityClass);

    // TODO: Configure the upsert options

    $columns = $this->inspector->getColumns(entity: $entityOrEntities);
    $updateColumns = $this->inspector->getColumns(entity: $entityOrEntities, exclude: $this->readonlyColumns);
    $values = $this->inspector->getValues(entity: $entityOrEntities);

    $assignmentList = array_map(fn($column) => "$column=VALUES($column)", $updateColumns);

    $result = $this
      ->query
      ->insertInto(tableName: $this->inspector->getTableName(entity: $entityOrEntities))
      ->singleRow(columns: $columns)
      ->values(valuesList: $values)
      ->onDuplicateKeyUpdate(
        assignmentList: $assignmentList,
      )->execute();

    $errors = [];
    if ($result->isError())
    {
      $errors[] = $this->query->getConnection()->errorInfo();
      $errors[] = new GeneralSQLQueryException($this->query);
    }

    $generatedMaps = $entityOrEntities;

    if ($result->isOk())
    {
      $generatedMaps->id = $this->lastInsertId();
    }

    return new InsertResult(
      identifiers: (object)['id' => $this->lastInsertId()],
      raw: $this->query->queryString(),
      generatedMaps: $entityOrEntities,
      errors: $errors
    );
  }

  /**
   * Removes a given entity from the database.
   *
   * @param object|object[] $entityOrEntities
   * @param RemoveOptions|null $removeOptions
   * @return DeleteResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function remove(
    object|array $entityOrEntities,
    ?RemoveOptions $removeOptions = null
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
      $removeResult = $this->remove(entityOrEntities: $entity, removeOptions: $removeOptions);
      $affected += $removeResult->affected;
      $raw .= $removeResult->raw . PHP_EOL;
    }

    return new DeleteResult(raw: $raw, affected: $affected);
  }

  /**
   * Records the deletion date of a given entity.
   *
   * @param object|object[] $entityOrEntities
   * @param RemoveOptions|array|null $removeOptions
   * @return UpdateResult Returns the removed entities.
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function softRemove(
    object|array $entityOrEntities,
    RemoveOptions|array|null $removeOptions = null
  ): UpdateResult
  {
    $result = null;
    $deletedAt = date(DATE_ATOM);

    if (is_object($entityOrEntities))
    {
      if (!$entityOrEntities->id)
      {
        throw new ORMException("Entity must have an id to be soft removed.");
      }

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
        raw: $this->query->queryString(),
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
   * @throws ReflectionException
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
   * @throws ReflectionException
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
   * @throws ReflectionException
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

    return $result->value()[0]?->total ?? 0;
  }

  /**
   * Find entities that match the given `FindOptions`.
   *
   * @param string $entityClass
   * @param null|FindOptions $findOptions
   *
   * @return FindResult<T>
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function find(string $entityClass, ?FindOptions $findOptions = new FindOptions()): FindResult
  {
    $entity = $this->create(entityClass: $entityClass);
    $conditions = [];
    $availableRelations = [];

    if ($deleteColumnName = $this->getDeleteDateColumnName(entityClass: $entityClass))
    {
      $conditions = array_merge($findOptions->where->conditions ?? $findOptions->where ?? [], [$deleteColumnName => 'NULL']);
    }

    $listOfRelations = match(gettype($findOptions->relations)) {
      'object' => (array)$findOptions->relations,
      'array' => $findOptions->relations,
      default => []
    };
    $columns =
      $this->inspector->getColumns(
        entity: $entity,
        exclude: $findOptions->exclude ?? [],
        relations: $listOfRelations,
        relationProperties: $availableRelations
      );

    $statement
      = $this
      ->query
      ->select()
      ->all(columns: $columns)
      ->from(tableReferences: $this->inspector->getTableName(entity: $entity));

    if (!empty($findOptions))
    {
      # Resolve relations and joins
      if ($findOptions->relations)
      {
        # $this->buildRelations($listOfRelations);
        foreach ($findOptions->relations as $key => $value)
        {
          /** @var RelationPropertyMetadata $relationProperty */
          $relationProperty = $availableRelations[$key] ?? $availableRelations[$value] ?? null;

          if (property_exists($entity, $key))
          {
            # TODO: Resolve relations custom options
          }

          if (!$relationProperty->getEntity())
          {
            continue;
          }

          # If one-to-one
          # LEFT JOIN
          $tableName = $this->inspector->getTableName($entity);
          $joinColumnName = $relationProperty->joinColumn->effectiveColumnName;
          $referencedColumnName = $relationProperty->joinColumn->referencedColumnName ?? 'id';
          $referencedTableName = $relationProperty->getEntity()->table;
          $referencedTableAlias = $referencedTableName;
          $statement =
            $statement->leftJoin("$referencedTableName $referencedTableAlias")
              ->on("$tableName.$joinColumnName=$referencedTableName.$referencedColumnName");
        }
      }

      # Resolve where conditions
      if ($conditions)
      {
        $findWhereOptions = new FindWhereOptions(conditions: $conditions, entityClass: $entityClass);
      }
      $statement = $statement->where(condition: $findWhereOptions ?? $findOptions);
    }

    $limit = $findOptions->limit ?? $_GET['limit'] ?? Config::get('DEFAULT_LIMIT') ?? 10;
    $skip = $findOptions->skip ?? $_GET['skip'] ?? Config::get('DEFAULT_SKIP') ?? 0;

    $result = $statement->limit(limit: $limit, offset: $skip)->execute();

    if ($result->isError())
    {
      return new FindResult(
        raw: $result->getRaw(),
        data: $result->value(),
        errors: [new GeneralSQLQueryException($this->query), ...$result->getErrors()]
      );
    }

    $resultSet = $this->processRelations($result->getData(), $entityClass, $findOptions, $availableRelations);

    return new FindResult(
      raw: $result->getRaw(),
      data: $resultSet,
      errors: $result->getErrors()
    );
  }

  /**
   * Finds entities that match given `FindWhereOptions`.
   *
   * @param string $entityClass The entity class name.
   * @param FindWhereOptions|array $where The find options.
   * @return FindResult Returns a FindResult object representing the result of the query.
   * @throws ClassNotFoundException If the given entity class does not exist.
   * @throws GeneralSQLQueryException If the query fails.
   * @throws ORMException|ReflectionException If the given entity class does not have the required attributes.
   */
  public function findBy(string $entityClass, FindWhereOptions|array $where): FindResult
  {
    $entity = $this->create(entityClass: $entityClass);
    if (is_array($where))
    {
      $where = $where['condition'] ?? '';
    }
    $statement =
      $this->query
        ->select()
        ->all(columns: $this->inspector->getColumns(entity: $entity, exclude: $where->exclude))
        ->from(tableReferences: $this->inspector->getTableName(entity: $entity))
        ->where(condition: $where);

    $limit = $_GET['limit'] ?? 100;
    $skip = $_GET['skip'] ?? 0;

    $result = $statement->limit(limit: $limit, offset: $skip)->execute();

    if ($result->isError())
    {
      throw new GeneralSQLQueryException($this->query);
    }

    return new FindResult(
      raw: $result->getRaw(),
      data: $result->getData(),
      errors: $result->getErrors()
    );
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
   * @throws ReflectionException
   */
  #[ArrayShape(['entities' => "array|null", 'count' => "int"])]
  public function findAndCount(
    string $entityClass,
    ?FindManyOptions $options = null
  ): array
  {
    $entities = $this->find(entityClass: $entityClass, findOptions: $options);

    return ['entities' => $entities, 'count' => count($entities->getData())];
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
   * @throws ReflectionException
   */
  #[ArrayShape(['entities' => "mixed", 'count' => "int"])]
  public function findAndCountBy(
    string $entityClass,
    FindWhereOptions|array $where
  ): array
  {
    $entities = $this->findBy(entityClass: $entityClass, where: $where);

    return ['entities' => $entities, 'count' => count($entities->getData())];
  }

  /**
   * Finds first entity by a given find options.
   * If entity was not found in the database - returns null.
   *
   * @param string $entityClass
   * @param FindOptions|FindOneOptions $options
   * @return T|null
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function findOne(
    string $entityClass,
    FindOptions|FindOneOptions $options
  ): FindResult
  {
    $result = $this->find(entityClass: $entityClass, findOptions: $options);

    if ($result->isError())
    {
      return $result;
    }

    if ($result->getTotal() === 0)
    {
      return new FindResult(raw: $result->getRaw(), data: null);
    }

    /** @var Entity $entityClass */
    return new FindResult(raw: $result->getRaw(), data: $result->getData()[0] ?? null);
  }

  /**
   * Sets the list of custom converters to use for type conversion.
   *
   * @param array $converters
   * @return void
   */
  public function useConverters(array $converters): void
  {
    $this->customConverters = $converters;
  }

  /**
   * Validates the given conditions. If the given conditions are invalid, then a `ValidationException` is thrown.
   *
   * @param string|object|array $conditions The conditions to validate.
   * @param string $methodName The name of the method that called this method.
   * @return void
   * @throws ValidationException If the given conditions are invalid.
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
   * Checks if the given object or class name is a valid Entity class name.
   *
   * @param string|object $objectOrClass The name of the class to check.
   * @return bool Returns `true` if the given class name is an Entity instance.
   * @throws ReflectionException
   */
  public static function objectOrClassIsEntity(string|object $objectOrClass): bool
  {
    $reflectionClass = new ReflectionClass($objectOrClass);
    $entityAttributes = $reflectionClass->getAttributes(Entity::class);

    if ($entityAttributes)
    {
      return true;
    }

    if ($objectOrClass instanceof Entity)
    {
      return true;
    }

    if (in_array(Entity::class, class_implements($objectOrClass)))
    {
      return true;
    }

    return false;
  }

  /**
   * @param string|object $objectOrClass
   * @return bool
   * @throws ReflectionException
   */
  public static function objectOrClassIsNotEntity(string|object $objectOrClass): bool
  {
    return ! self::objectOrClassIsEntity(objectOrClass: $objectOrClass);
  }

  /**
   * @return array|string[]
   */
  public function getSecure(): array
  {
    return array_merge($this->secure, $this->customSecure);
  }

  /**
   * @param array|string[] $secure
   */
  public function setSecure(array $secure): void
  {
    $this->customSecure = $secure;
  }

  /**
   * @param string $entityClassName
   * @param object $object
   * @param IFactory|null $factory
   * @return object
   * @throws ClassNotFoundException
   * @throws ORMException
   * @throws ReflectionException
   * @throws ContainerException
   */
  public function getEntityFromObject(string $entityClassName, object $object, ?IFactory $factory = null): object
  {
    self::validateEntityName($entityClassName);

    $provider = new EntityProvider($this, $factory);
    $entity = $provider->get($entityClassName);

    foreach ($object as $prop => $value)
    {
      if (property_exists($entity, $prop))
      {
        $sourceReflection = new ReflectionProperty($object, $prop);
        $targetReflection = new ReflectionProperty($entity, $prop);

        $sourceType = $sourceReflection->getType()?->getName() ?? match(gettype($object->$prop)) {
            'integer' => 'int',
            'double' => 'float',
            'NULL' => null,
            default => gettype($object->$prop)
          };
        $targetType = $targetReflection->getType()?->getName() ?? match(gettype($entity->$prop)) {
            'integer' => 'int',
            'double' => 'float',
            'NULL' => null,
            default => gettype($entity->$prop)
        };

        if (is_null($sourceType) || is_null($targetType))
        {
          continue;
        }

        if ($sourceType !== $targetType)
        {
          $value = $this->castValue(value: $value, sourceType: $sourceType, targetType: $targetType);
        }

        $entity->$prop = $value;
      }
    }

    return $entity;
  }

  /**
   * @param string|null $name
   * @return array
   */
  public function getStore(?string $name = null): array
  {
    return $this->entities;
  }

  /**
   * @param string $key
   * @return object|null
   */
  public function getStoreEntry(string $key): ?object
  {
    return $this->hasStoreEntry($key) ? $this->getStoreEntry($key) : null;
  }

  /**
   * @param string $key
   * @param object $value
   * @return int
   */
  public function addStoreEntry(string $key, object $value): int
  {
    return count($this->entities);
  }

  /**
   * @param string $key
   * @param object $value
   * @return int
   */
  public function removeStoreEntry(string $key, object $value): int
  {
    return count($this->entities);
  }

  /**
   * @param string $key
   * @return bool
   */
  public function hasStoreEntry(string $key): bool
  {
    return isset($this->entities[$key]);
  }

  /**
   * @throws ReflectionException
   * @throws TypeConversionException
   */
  private function castValue(mixed $value, string $sourceType, string $targetType): mixed
  {
    if (is_null($value))
    {
      return null;
    }

    foreach ($this->customConverters as $converter)
    {
      $result =
        $this->typeResolver->resolve(
          converterHost: $converter, value: $value, fromType: $sourceType, toType: $targetType
        );

      if (! is_null($result) )
      {
        return $result;
      }
    }

    foreach ($this->defaultConverters as $converter)
    {
      $result =
        $this->typeResolver->resolve(
          converterHost: $converter, value: $value, fromType: $sourceType, toType: $targetType
        );

      if (! is_null($result) )
      {
        return $result;
      }
    }

    return null;
  }

  /**
   * @param string|ReflectionClass $entityClass
   * @return string|null
   * @throws ReflectionException
   */
  private function getDeleteDateColumnName(string|ReflectionClass $entityClass): ?string
  {
    # If $entityClass is a string, get a reflection class
    $reflection = is_string($entityClass) ? new ReflectionClass($entityClass) : $entityClass;

    # Get properties
    $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

    foreach ($properties as $property)
    {
      # Find DeleteDateColumn attribute
      $deleteDateColumnAttributes = $property->getAttributes(DeleteDateColumn::class);

      if (empty($deleteDateColumnAttributes))
      {
        continue;
      }

      # If name is specified use name, else use property name
      $columnInstance = $deleteDateColumnAttributes[0]->newInstance();

      return $columnInstance->name ?? $property->getName();
    }

    return null;
  }

  /**
   * @param object $relations
   * @param object $selection
   * @param EntityMetadata $metadata
   * @param string $alias
   * @param string|null $embedPrefix
   * @return void
   */
  private function buildRelations(
    object $relations,
    object $selection,
    EntityMetadata $metadata,
    string $alias,
    ?string $embedPrefix
  ): void
  {
    // TODO: Implement buildRelations() method.
    if (!$relations)
    {
      return;
    }

    foreach ($relations as $relationName => $relationValue)
    {
      $propertyPath = $embedPrefix ? "$embedPrefix.$relationName" : $relationName;
//      $embed = $metadata->em
    }
  }

  /**
   * @param array $data
   * @param string $entityClass
   * @param FindWhereOptions|FindOptions|null $findOptions
   * @param RelationPropertyMetadata[] $relationInfo
   * @return array
   */
  private function processRelations(
    array $data,
    string $entityClass,
    FindWhereOptions|FindOptions|null $findOptions,
    array $relationInfo
  ): array
  {
    if (!$findOptions || !$findOptions->relations)
    {
      return $data;
    }

    $results = [];

    foreach ($data as $datum)
    {
      foreach ($findOptions->relations as $relation)
      {
        $results[] = $this->restructureRelatedEntity($entityClass, $datum, $relation, $relationInfo[$relation]);
      }
    }

    return $results;
  }

  /**
   * @param string $entityClass
   * @param object $entity
   * @param string $relationName
   * @param RelationPropertyMetadata $relationInfo
   * @return object
   */
  private function restructureRelatedEntity(
    string       $entityClass,
    object       $entity,
    string       $relationName,
    RelationPropertyMetadata $relationInfo
  ): object
  {
    $restructuredEntity = new stdClass();
    $relation = new stdClass();
//      $restructuredEntity = $this->create($entityClass);
//      $relation = $this->create($relationInfo->relationAttribute->type);

    # Foreach relation
    foreach ($entity as $key => $value)
    {
      $tableName = $relationInfo->getEntity()->table;
      $pattern = "/{$tableName}_|$relationName/";

      if (preg_match($pattern, $key))
      {
        if (!$restructuredEntity->$relationName)
        {
          $restructuredEntity->$relationName = $relation;
        }

        $name = lcfirst(preg_replace($pattern, '$2', $key));
        $relation->$name = $value;
      }
      else
      {
        $restructuredEntity->$key = $value;
      }
    }
    $restructuredEntity->$relationName = $relation;

    if (!$restructuredEntity->$relationName->id)
    {
      $restructuredEntity->$relationName = null;
    }

    return $restructuredEntity;
  }

  /**
   * Returns the default value for a given column. If no default value is specified, returns null.
   *
   * @param ReflectionProperty $reflectionProperty The reflection property to get the default value for.
   * @return mixed Returns the default value for the given column.
   * @throws ValidationException If the default value is invalid.
   */
  private function getDefaultColumnValue(ReflectionProperty $reflectionProperty): mixed
  {
    # Load Column attribute for the property
    $attributes = $reflectionProperty->getAttributes();

    foreach ($attributes as $attribute)
    {
      $attributeInstance = $attribute->newInstance();

      try
      {
        # Check if a default value is specified
        if (!is_null($attributeInstance->default))
        {
          return match (true) {
            $attributeInstance->default === 'CURRENT_TIMESTAMP',
              $attributeInstance->default === 'NOW()' => new DateTime(),
            # Match ISO 8601 date format
            preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $attributeInstance->default) => new DateTime($attributeInstance->default),
            # Match ATOM date format
            preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d+$/', $attributeInstance->default) => new DateTime($attributeInstance->default),
            default => $attributeInstance->default
          };
        }
      }
      catch (Exception $exception)
      {
        throw new ValidationException($exception);
      }
    }

    # If no default value is specified, return null
    return null;
  }

  /**
   * Returns the column name for a given property. If no column name is specified, returns the property name.
   *
   * @param object $entity The entity to get the column name for.
   * @param string $prop The property to get the column name for.
   * @return string Returns the column name for the given property.
   * @throws ReflectionException If the property does not exist.
   */
  private function getColumnNameFromProperty(object $entity, string $prop): string
  {
    $propertyReflection = new ReflectionProperty($entity, $prop);
    $attributes = $propertyReflection->getAttributes();

    if (empty($attributes))
    {
      return $prop;
    }

    # Return $prop if no valid Column attribute found
    $columnClassNames = [
      Column::class,
      CreateDateColumn::class,
      DeleteDateColumn::class,
      EmailColumn::class,
      PasswordColumn::class,
      PrimaryGeneratedColumn::class,
      UpdateDateColumn::class,
      URLColumn::class
    ];
    $columnAttribute = null;
    foreach ( $attributes as $attribute)
    {
      if (in_array($attribute->getName(), $columnClassNames))
      {
        $columnAttribute = $attribute;
      }
    }

    if (!$columnAttribute)
    {
      return $prop;
    }

    /** @var Column $column */
    $column = $columnAttribute->newInstance();
    return empty($column->name) ? $prop : $column->name;
  }

  /**
   * Determines if a given column map contains a given column name.
   *
   * @param array $columnMap The column map to check.
   * @param string $columnName The column name to check for.
   * @return bool Returns true if the column map contains the given column name, otherwise false.
   */
  private function mapContainsColumnName(array $columnMap, string $columnName): bool
  {
    foreach ($columnMap as $key => $value)
    {
      if (str_ends_with($key, $columnName))
      {
        return true;
      }

      if (str_ends_with($value, $columnName))
      {
        return true;
      }
    }
    return false;
  }
}