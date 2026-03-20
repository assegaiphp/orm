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
use Assegai\Orm\Attributes\Relations\JoinColumn;
use Assegai\Orm\Attributes\Relations\JoinTable;
use Assegai\Orm\Attributes\Relations\ManyToOne;
use Assegai\Orm\Attributes\Relations\OneToMany;
use Assegai\Orm\Attributes\Relations\OneToOne;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\RelationType;
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
use Assegai\Orm\Management\Options\InsertOptions;
use Assegai\Orm\Management\Options\RemoveOptions;
use Assegai\Orm\Management\Options\UpdateOptions;
use Assegai\Orm\Management\Options\UpsertOptions;
use Assegai\Orm\Metadata\RelationPropertyMetadata;
use Assegai\Orm\Queries\QueryBuilder\Results\DeleteResult;
use Assegai\Orm\Queries\QueryBuilder\Results\FindResult;
use Assegai\Orm\Queries\QueryBuilder\Results\InsertResult;
use Assegai\Orm\Queries\QueryBuilder\Results\UpdateResult;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLQueryResult;
use Assegai\Orm\Relations\RelationOptions;
use Assegai\Orm\Util\Filter;
use Assegai\Orm\Util\Log\Logger;
use Assegai\Orm\Util\SqlIdentifier;
use Assegai\Orm\Util\TypeConversion\BasicTypeConverter;
use Assegai\Orm\Util\TypeConversion\TypeResolver;
use DateInvalidTimeZoneException;
use DateMalformedStringException;
use DateTime;
use DateTimeZone;
use DateTimeImmutable;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use NumberFormatter;
use PDOStatement;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use ReflectionUnionType;
use stdClass;
use Symfony\Component\Console\Output\ConsoleOutput;
use UnitEnum;

/**
 * Class EntityManager. The EntityManager is the central access point to ORM functionality.
 * @package Assegai\Orm\Management
 *
 * @template T of object
 */
class EntityManager implements IEntityStoreOwner
{
  const string LOG_TAG = '[Entity Manager]';
  const string DEFAULT_TIMEZONE = 'UTC';
  const string DEFAULT_DELETED_AT_FORMAT = 'Y-m-d H:i:s';

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
   * @var BasicTypeConverter[] An array of custom converters.
   */
  protected array $customConverters = [];
  /**
   * @var bool Is debug mode enabled.
   */
  protected bool $isDebug = false;
  /**
   * @var LoggerInterface The logger instance.
   */
  protected LoggerInterface $logger;

  /**
   * Constructs a new EntityManager instance.
   *
   * @param DataSource $connection The data source to use.
   * @param SQLQuery|null $query The query to use.
   * @param EntityInspector|null $entityInspector The entity inspector to use.
   * @param TypeResolver|null $typeResolver The type resolver to use.
   * @throws ReflectionException
   */
  public function __construct(protected DataSource $connection, protected ?SQLQuery $query = null, protected ?EntityInspector $entityInspector = null, protected ?TypeResolver $typeResolver = null)
  {
    $this->logger = new Logger(new ConsoleOutput());
    $this->query = $query ?? new SQLQuery(db: $connection->getClient());

    // TODO: *BREAKING_CHANGE* Remove this binding as it breaks the inversion of control principal
    if (!$this->entityInspector) {
      $this->entityInspector = EntityInspector::getInstance();
      $this->entityInspector->setLogger($this->logger);
    }

    // TODO: *BREAKING_CHANGE* Remove this binding as it breaks the inversion of control principal
    if (!$this->typeResolver) {
      $this->typeResolver = TypeResolver::getInstance();
    }

    $this->defaultConverters[] = new BasicTypeConverter();
    if ($customConverters = ModuleManager::getInstance()->getConfig('converters')) {
      foreach ($customConverters as $converterClassName) {
        $converterReflection = new ReflectionClass($converterClassName);
        $customConvertor = $converterReflection->newInstance();
        $this->customConverters[] = $customConvertor;
      }
    }

    $isDebugMode = filter_var($_ENV['DEBUG_MODE'] ?? false, FILTER_VALIDATE_BOOL);
    $environment = strtoupper($_ENV['ENV'] ?? 'development');
    $this->isDebug = !in_array($environment, ['PROD', 'PRODUCTION']) && $isDebugMode === true;
  }

  /**
   * @param string|object $objectOrClass
   * @return bool
   * @throws ReflectionException
   */
  public static function objectOrClassIsNotEntity(string|object $objectOrClass): bool
  {
    return !self::objectOrClassIsEntity(objectOrClass: $objectOrClass);
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

    if ($entityAttributes) {
      return true;
    }

    if ($objectOrClass instanceof Entity) {
      return true;
    }

    if (in_array(Entity::class, class_implements($objectOrClass))) {
      return true;
    }

    return false;
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
    if (empty($parameters)) {
      return $this->connection->getClient()->query($query);
    }

    $statement = $this->connection->getClient()->prepare($query);

    if ($statement === false) {
      return false;
    }

    if (!$statement->execute($parameters)) {
      return false;
    }

    return $statement;
  }

  /**
   * Saves all given entities in the database.
   * If entities do not exist in the database then inserts, otherwise updates.
   *
   * @param object|object[] $targetOrEntity The entity or entities to save.
   * @param InsertOptions|UpdateOptions|null $options The insert options.
   * @return QueryResultInterface
   * @throws ClassNotFoundException
   * @throws EmptyCriteriaException
   * @throws GeneralSQLQueryException
   * @throws IllegalTypeException
   * @throws ORMException
   * @throws ReflectionException
   * @throws SaveException
   * @throws ValidationException
   */
  public function save(object|array $targetOrEntity, InsertOptions|UpdateOptions|null $options = null): QueryResultInterface
  {
    $results = [];
    $primaryKeyField = $options->primaryKeyField ?? 'id';
    $primaryKeyColumn = $this->getPrimaryKeyColumnName(entity: is_object($targetOrEntity) ? $targetOrEntity : $targetOrEntity[0], primaryKeyField: $primaryKeyField);

    /** @var object $targetOrEntity */
    if (is_object($targetOrEntity)) {
      if (empty($targetOrEntity->{$primaryKeyField})) {
        if (!$options instanceof InsertOptions) {
          $this->logger->warning("InsertOptions not provided. Using default InsertOptions.");
          $options = new InsertOptions();
        }

        $saveResult = $this->insert(entityClass: $targetOrEntity::class, entity: $targetOrEntity, options: $options);
      } else if ($this->findBy($targetOrEntity::class, new FindWhereOptions(conditions: ['id' => $targetOrEntity->{$primaryKeyField}]))) {
        if (!$options instanceof UpdateOptions) {
          $this->logger->warning("UpdateOptions not provided. Using default UpdateOptions.");
          $options = new UpdateOptions();
        }

        $saveResult = $this->update(entityClass: $targetOrEntity::class, partialEntity: $targetOrEntity, conditions: ['id' => $targetOrEntity->id], options: $options);
      } else {
        $saveResult = new SQLQueryResult([], [new NotFoundException($targetOrEntity->id)]);
      }

      if ($saveResult instanceof InsertResult || $saveResult instanceof UpdateResult || $saveResult instanceof DeleteResult) {
        return $saveResult;
      }

      return $this->findBy($targetOrEntity::class, new FindWhereOptions(conditions: ['id' => $this->query->lastInsertId()]));
    }

    foreach ($targetOrEntity as $entity) {
      $results[] = $this->save(targetOrEntity: $entity);
    }

    return new SQLQueryResult($results);
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
   * @param string $entityClass The entity class name.
   * @param array|object $entity The entity to insert.
   * @param InsertOptions|null $options The options to use when inserting the entity.
   * @return InsertResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function insert(string $entityClass, array|object $entity, ?InsertOptions $options = null): InsertResult
  {
    # Check if the entity matches the given entity class
    if (!$this->entityInspector->hasValidEntityStructure(entity: $entity, entityClass: $entityClass)) {
      return new InsertResult(identifiers: $entity, raw: $this->query->queryString(), generatedMaps: null, errors: [new ORMException("Entity does not match the given entity class.")]);
    }

    $instance = $this->create(entityClass: $entityClass, entityLike: (object)$entity);
    $columnsMeta = [];
    $relations = [];
    $relationProperties = [];

    if ($options?->relations) {
      $relations = is_object($options->relations) ? (array)$options->relations : $options->relations;
    }

    $columns = $this->entityInspector->getColumns(entity: $instance, exclude: $options->readonlyColumns ?? $this->readonlyColumns, relations: $relations, relationProperties: $relationProperties, meta: $columnsMeta);
    $values = $this->entityInspector->getValues(entity: $instance, exclude: $options->readonlyColumns ?? $this->readonlyColumns, options: ['relations' => $relations, 'relation_properties' => $relationProperties, 'filter' => true, 'column_types' => $columnsMeta['column_types'] ?? []]);

    $columnCount = count($columns);
    $valueCount = count($values);

    $this->query->insertInto(tableName: $this->entityInspector->getTableName(entity: $instance))->singleRow(columns: $columns)->values(valuesList: $values);

    if ($this->isDebug || $options?->isDebug) {
      $this->query->debug();
    }

    $result = $this->query->execute();

    if ($result->isError()) {
      if (!headers_sent()) {
        http_response_code(500);
      }
      $error = new GeneralSQLQueryException($this->query);
      Log::error(self::LOG_TAG, $error);

      return new InsertResult(identifiers: $entity, raw: $this->query->queryString(), generatedMaps: $result, errors: [$error]);
    }

    # Find the record by the last insert id and hydrate the entity
    $result = $this->findOne(entityClass: $entityClass, options: new FindOneOptions(where: ['id' => $this->lastInsertId()]));

    if ($result->isError()) {
      if (!headers_sent()) {
        http_response_code(500);
      }
      $error = new GeneralSQLQueryException($this->query);
      Log::error(self::LOG_TAG, $error);

      return new InsertResult(identifiers: $entity, raw: $this->query->queryString(), generatedMaps: $result, errors: [$result->getErrors()]);
    }

    $entity = is_array($result->getData()) && array_is_list($result->getData())
      ? ($result->getData()[0] ?? null)
      : $result->getData();

    $generatedMaps = (object)array_merge((array)$entity, (array)new stdClass());
    $generatedMaps->id = $this->lastInsertId();

    foreach ($generatedMaps as $prop => $value) {
      if (in_array($prop, $this->getSecure())) {
        unset($generatedMaps->$prop);
      }
    }

    $identifiers = is_array($entity) ? (object)$entity : $entity;

    return new InsertResult(identifiers: $identifiers, raw: $this->query->queryString(), generatedMaps: $generatedMaps);
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

    if (!empty($entityLike)) {
      foreach ($entityLike as $entityLikePropertyName => $entityLikePropertyValue) {
        if (property_exists($entity, $entityLikePropertyName)) {
          $entityLikeReflectionProperty = new ReflectionProperty($entityLike, $entityLikePropertyName);
          $entityClassReflectionProperty = new ReflectionProperty($entityClass, $entityLikePropertyName);

          if (is_null($entityLikePropertyValue)) {
            $entityLikePropertyValue = $this->getDefaultColumnValue($entityClassReflectionProperty);

            if (!$entityClassReflectionProperty->getType()->allowsNull()) {
              continue;
            }
          }

          $entityLikeReflectionPropertyType = match (true) {
            $entityLikeReflectionProperty->getType() instanceof ReflectionUnionType => strtolower(gettype($entityLikePropertyValue)),
            default => $entityLikeReflectionProperty->getType()?->getName()
          };
          $entityClassReflectionPropertyType = match (true) {
            $entityClassReflectionProperty->getType() instanceof ReflectionUnionType => strval($entityClassReflectionProperty->getType()),
            default => $entityClassReflectionProperty->getType()?->getName()
          };
          $typesMatch = ($entityLikeReflectionPropertyType === $entityClassReflectionPropertyType) || ( (!empty($entityClassReflectionPropertyType) && !empty($entityLikeReflectionPropertyType))  && str_contains($entityClassReflectionPropertyType, ($entityLikeReflectionPropertyType ?? '')) );

          $sourceType = $entityLikeReflectionPropertyType ?? gettype($entityLikePropertyValue);
          $targetType = $entityClassReflectionPropertyType ?? gettype($entityLikePropertyValue);

          $entity->$entityLikePropertyName = $typesMatch ? $entityLikePropertyValue : $this->castValue(value: $entityLikePropertyValue, sourceType: $sourceType, targetType: $targetType) ?? $entityLikePropertyValue;
        }
      }
    }

    return $entity;
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

    foreach ($attributes as $attribute) {
      $attributeInstance = $attribute->newInstance();

      if (!property_exists($attributeInstance, 'default')) {
        continue;
      }

      try {
        # Check if a default value is specified
        if (!is_null($attributeInstance->default)) {
          return match (true) {
            $attributeInstance->default === 'CURRENT_TIMESTAMP', $attributeInstance->default === 'NOW()' => new DateTime(),
            # Match ISO 8601 date format
            preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $attributeInstance->default) => new DateTime($attributeInstance->default),
            # Match ATOM date format
            preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d+$/', $attributeInstance->default) => new DateTime($attributeInstance->default),
            default => $attributeInstance->default
          };
        }
      } catch (Exception $exception) {
        throw new ValidationException($exception);
      }
    }

    # If no default value is specified, return null
    return null;
  }

  /**
   * @throws ReflectionException
   * @throws TypeConversionException
   */
  private function castValue(mixed $value, string $sourceType, string $targetType): mixed
  {
    if (is_null($value)) {
      return null;
    }

    foreach ($this->customConverters as $converter) {
      $result = $this->typeResolver->resolve(converterHost: $converter, value: $value, fromType: $sourceType, toType: $targetType);

      if (!is_null($result)) {
        return $result;
      }
    }

    foreach ($this->defaultConverters as $converter) {
      $result = $this->typeResolver->resolve(converterHost: $converter, value: $value, fromType: $sourceType, toType: $targetType);

      if (!is_null($result)) {
        return $result;
      }
    }

    return null;
  }

  /**
   * Finds first entity by a given find options.
   * If entity was not found in the database - returns null.
   *
   * @param string $entityClass
   * @param FindOptions|FindOneOptions $options
   * @return FindResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function findOne(string $entityClass, FindOptions|FindOneOptions $options): FindResult
  {
    $result = $this->find(entityClass: $entityClass, findOptions: $options);

    if ($result->isError()) {
      return $result;
    }

    if ($result->getTotal() === 0) {
      return new FindResult(raw: $result->getRaw(), data: null);
    }

    /** @var Entity $entityClass */
    return new FindResult(raw: $result->getRaw(), data: $result->getData()[0] ?? null);
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
    $requestedRelations = $this->getRequestedRelationNames($findOptions);
    $baseExcludeColumns = $this->resolveBaseEntityExcludeColumns($findOptions, $requestedRelations);

    if ($deleteColumnName = $this->getDeleteDateColumnName(entityClass: $entityClass)) {
      $conditions = array_merge($findOptions->where->conditions ?? $findOptions->where ?? [], [$deleteColumnName => 'NULL']);
    }

    $columns = $this->entityInspector->getColumns(
      entity: $entity,
      exclude: $baseExcludeColumns,
      relations: $requestedRelations,
      relationProperties: $availableRelations
    );
    $columns = $this->appendRequiredRelationColumns($entity, $columns, $availableRelations);

    $tableName = $this->entityInspector->getTableName(entity: $entity);
    $statement = $this->query->select()->all(columns: $columns)->from(tableReferences: $tableName);

    if (!empty($findOptions)) {
      foreach ($requestedRelations as $relationName) {
        if (!isset($availableRelations[$relationName])) {
          if ($this->isDebug) {
            throw new ORMException("Relation $relationName does not exist in the entity $entityClass.");
          }

          $this->logger->warning("Relation $relationName does not exist in the entity $entityClass. \n\tThrown in " . __FILE__ . ' on line ' . __LINE__);
        }
      }

      # Resolve where conditions
      if ($conditions) {
        $findWhereOptions = new FindWhereOptions(conditions: $conditions, entityClass: $entityClass);
      }

      $statement = $statement->where(condition: $findWhereOptions ?? $findOptions);
    }

    if ($findOptions->order) {
      $statement->orderBy($findOptions->order);
    }

    $limit = $findOptions->limit ?? $_GET['limit'] ?? Config::get('DEFAULT_LIMIT') ?? 10;
    $skip = $findOptions->skip ?? $_GET['skip'] ?? Config::get('DEFAULT_SKIP') ?? 0;

    $statement = $statement->limit(limit: $limit, offset: $skip);

    if ($this->isDebug || $findOptions->isDebug) {
      $statement->debug();
    }

    $result = $statement->execute();

    if ($result->isError()) {
      return new FindResult(raw: $result->getRaw(), data: $result->value(), errors: [new GeneralSQLQueryException($this->query), ...$result->getErrors()], affected: $result->getTotalAffectedRows());
    }

    $loadedRelations = $this->loadRequestedRelations($entityClass, $result->getData(), $findOptions, $availableRelations);
    $resultSet = $this->processRelations($result->getData(), $entityClass, $findOptions, $availableRelations, $loadedRelations);
    $resultSet = $this->stripExcludedColumns($resultSet, $findOptions->exclude ?? []);

    if ($findOptions->withRealTotal) {
      $total = $this->count(entityClass: $entityClass, options: $findOptions);

      if (count($resultSet) === 0) {
        $total = 0;
      }

      return new FindResult(raw: $result->getRaw(), data: $resultSet, errors: $result->getErrors(), total: $total);
    }

    return new FindResult(raw: $result->getRaw(), data: $resultSet, errors: $result->getErrors());
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

    foreach ($properties as $property) {
      # Find DeleteDateColumn attribute
      $deleteDateColumnAttributes = $property->getAttributes(DeleteDateColumn::class);

      if (empty($deleteDateColumnAttributes)) {
        continue;
      }

      # If name is specified use name, else use property name
      $columnInstance = $deleteDateColumnAttributes[0]->newInstance();

      return $columnInstance->name ?? $property->getName();
    }

    return null;
  }

  /**
   * @param FindOptions|null $findOptions
   * @return string[]
   */
  protected function getRequestedRelationNames(?FindOptions $findOptions): array
  {
    $relations = $findOptions?->relations ?? [];

    if (!is_array($relations)) {
      $relations = (array)$relations;
    }

    if (array_is_list($relations)) {
      return array_values(
        array_filter(
          array_map(
            fn($relation) => is_string($relation) ? trim($relation) : null,
            $relations
          )
        )
      );
    }

    $requestedRelations = [];

    foreach ($relations as $relation => $enabled) {
      if (!is_string($relation)) {
        continue;
      }

      if ($enabled) {
        $requestedRelations[] = trim($relation);
      }
    }

    return $requestedRelations;
  }

  /**
   * Keeps relation keys available internally even when callers exclude them from the final payload.
   *
   * @param FindOptions|null $findOptions
   * @param string[] $requestedRelations
   * @return string[]
   */
  private function resolveBaseEntityExcludeColumns(?FindOptions $findOptions, array $requestedRelations): array
  {
    $excludeColumns = $findOptions?->exclude ?? [];

    if (empty($excludeColumns) || empty($requestedRelations)) {
      return $excludeColumns;
    }

    $requiredColumns = array_unique(array_merge(['id'], $requestedRelations));

    return array_values(
      array_filter(
        $excludeColumns,
        fn(string $column): bool => !in_array($column, $requiredColumns, true)
      )
    );
  }

  /**
   * Ensures relation loading can still read required local keys from the root entity rows.
   *
   * @param array<int|string, string> $columns
   * @param array<string, RelationPropertyMetadata> $availableRelations
   * @return array<int|string, string>
   * @throws ORMException
   * @throws ReflectionException
   */
  private function appendRequiredRelationColumns(object $entity, array $columns, array $availableRelations): array
  {
    foreach ($availableRelations as $relationProperty) {
      if (!$relationProperty instanceof RelationPropertyMetadata) {
        continue;
      }

      $columns = match ($relationProperty->getRelationType()) {
        RelationType::ONE_TO_ONE => $relationProperty->joinColumn
          ? $this->appendJoinColumnSelection(
            columns: $columns,
            entityClass: $entity::class,
            propertyName: $relationProperty->name,
            joinColumn: $relationProperty->joinColumn
          )
          : $this->appendPropertyColumnSelection(
            columns: $columns,
            entity: $entity,
            propertyName: 'id'
          ),
        RelationType::ONE_TO_MANY => $this->appendPropertyColumnSelection(
          columns: $columns,
          entity: $entity,
          propertyName: $relationProperty->relationAttribute->referencedProperty ?? 'id'
        ),
        RelationType::MANY_TO_ONE => $this->appendJoinColumnSelection(
          columns: $columns,
          entityClass: $entity::class,
          propertyName: $relationProperty->name,
          joinColumn: $relationProperty->joinColumn
            ?? $this->entityInspector->getJoinColumnAttribute($entity::class, $relationProperty->name)
        ),
        RelationType::MANY_TO_MANY => $this->appendPropertyColumnSelection(
          columns: $columns,
          entity: $entity,
          propertyName: 'id'
        ),
        default => $columns,
      };
    }

    return $columns;
  }

  /**
   * @param array<int|string, string> $columns
   * @return array<int|string, string>
   */
  private function appendPropertyColumnSelection(array $columns, object $entity, string $propertyName): array
  {
    if (!property_exists($entity, $propertyName)) {
      return $columns;
    }

    $reflectionProperty = new ReflectionProperty($entity, $propertyName);

    foreach ($reflectionProperty->getAttributes() as $attribute) {
      $attributeInstance = $attribute->newInstance();

      if (!$attributeInstance instanceof Column) {
        continue;
      }

      $tableName = $this->entityInspector->getTableName($entity);
      $columnName = $attributeInstance->name ?? $propertyName;
      $columnAlias = $attributeInstance->alias ?: ($attributeInstance->name ? $propertyName : null);

      return $this->appendColumnSelection($columns, $columnAlias, "$tableName.$columnName");
    }

    return $columns;
  }

  /**
   * @param array<int|string, string> $columns
   * @return array<int|string, string>
   * @throws ORMException
   */
  private function appendJoinColumnSelection(array $columns, string $entityClass, string $propertyName, JoinColumn $joinColumn): array
  {
    $tableName = $this->entityInspector->getTableName($this->create($entityClass));
    $columnName = $joinColumn->effectiveColumnName ?? $joinColumn->name ?? $propertyName;

    return $this->appendColumnSelection($columns, $propertyName, "$tableName.$columnName");
  }

  /**
   * @param array<int|string, string> $columns
   * @return array<int|string, string>
   */
  private function appendColumnSelection(array $columns, ?string $columnAlias, string $columnSelection): array
  {
    if (
      ($columnAlias !== null && array_key_exists($columnAlias, $columns)) ||
      in_array($columnSelection, $columns, true) ||
      in_array($columnSelection, array_values($columns), true)
    ) {
      return $columns;
    }

    if ($columnAlias === null) {
      $columns[] = $columnSelection;
      return $columns;
    }

    $columns[$columnAlias] = $columnSelection;

    return $columns;
  }

  /**
   * @param object[] $rows
   * @param string[] $excludeColumns
   * @return object[]
   */
  private function stripExcludedColumns(array $rows, array $excludeColumns): array
  {
    if (empty($excludeColumns)) {
      return $rows;
    }

    return array_map(function(object|array $row) use ($excludeColumns): object {
      $row = is_array($row) ? (object)$row : $row;

      foreach ($excludeColumns as $column) {
        unset($row->{$column});
      }

      return $row;
    }, $rows);
  }

  /**
   * Generates an alias for a given table name.
   *
   * @param string $tableName The table name to generate an alias for.
   * @param array $knownAliases The list of known aliases.
   * @return string Returns the generated alias.
   */
  private function generateAlias(string $tableName, array &$knownAliases): string
  {
    $tableNameLetters = str_split($tableName);
    $alias = '';

    foreach ($tableNameLetters as $letter) {
      $alias .= $letter;
      if (!in_array($alias, $knownAliases)) {
        $knownAliases[] = $alias;
        return $alias;
      }
    }

    return $alias;
  }

  /**
   * Processes the relations of the given data.
   *
   * @param object[] $data The data to process.
   * @param string $entityClass The entity class name.
   * @param FindWhereOptions|FindOptions|null $findOptions The find options.
   * @param RelationPropertyMetadata[] $relationInfo The relation information.
   * @param array $loadedRelations The loaded relations.
   * @return object[] Returns the processed data.
   */
  private function processRelations(array $data, string $entityClass, FindWhereOptions|FindOptions|null $findOptions, array $relationInfo, array $loadedRelations): array
  {
    if (!$findOptions || !$findOptions->relations) {
      return $data;
    }

    $results = [];
    $requestedRelations = $this->getRequestedRelationNames($findOptions);

    foreach ($data as $datum) {
      if (is_array($datum)) {
        $datum = (object)$datum;
      }

      foreach ($requestedRelations as $relation) {
        if (!isset($relationInfo[$relation])) {
          $this->logger->warning("Relation $relation does not exist in the entity $entityClass. \n\tThrown in " . __FILE__ . ' on line ' . __LINE__);
          continue;
        }

        $datum = $this->bindEntityRelations($entityClass, $datum, $relation, $relationInfo[$relation], $loadedRelations);
      }

      $results[] = $datum;
    }

    return $results;
  }

  /**
   * Binds entity relations to the entity.
   *
   * @param string $entityClass The entity class name.
   * @param object $entity The entity to bind the relations to.
   * @param string $relationName The name of the relation to bind.
   * @param RelationPropertyMetadata $relationInfo The relation information.
   * @param array<string, mixed> $loadedRelations The loaded relations. This list is used to prevent previously loaded relations from being loaded again.
   * @return object Returns the entity with the relations bound.
   */
  private function bindEntityRelations(string $entityClass, object $entity, string $relationName, RelationPropertyMetadata $relationInfo, array $loadedRelations): object
  {
    if ($this->isDebug) {
      $this->logger->debug("Restructuring entity $entityClass with relation $relationName");
    }

    $entity = is_array($entity) ? (object)$entity : $entity;
    $relationValue = match ($relationInfo->getRelationType()) {
      RelationType::ONE_TO_ONE => $this->resolveOneToOneRelationValue($entity, $relationInfo, $loadedRelations[$relationName] ?? []),
      RelationType::ONE_TO_MANY => $this->resolveOneToManyRelationValue($entity, $relationInfo, $loadedRelations[$relationName] ?? []),
      RelationType::MANY_TO_ONE => $this->resolveManyToOneRelationValue($entity, $relationInfo, $loadedRelations[$relationName] ?? []),
      RelationType::MANY_TO_MANY => $this->resolveManyToManyRelationValue($entity, $loadedRelations[$relationName] ?? []),
      default => null,
    };

    $entity->$relationName = $relationValue;
    return $entity;
  }

  /**
   * @param array<int, object|array> $data
   * @param array<string, RelationPropertyMetadata> $availableRelations
   * @return array<string, array<mixed, mixed>>
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  private function loadRequestedRelations(string $entityClass, array $data, ?FindOptions $findOptions, array $availableRelations): array
  {
    if (!$findOptions || !$findOptions->relations || empty($data)) {
      return [];
    }

    $loadedRelations = [];
    $rows = array_map(
      fn(object|array $row) => is_array($row) ? (object)$row : $row,
      $data
    );

    foreach ($this->getRequestedRelationNames($findOptions) as $relationName) {
      $relationProperty = $availableRelations[$relationName] ?? null;

      if (!$relationProperty instanceof RelationPropertyMetadata) {
        continue;
      }

      $loadedRelations[$relationName] = match ($relationProperty->getRelationType()) {
        RelationType::ONE_TO_ONE => $this->loadOneToOneRelation($entityClass, $rows, $relationProperty, $findOptions),
        RelationType::ONE_TO_MANY => $this->loadOneToManyRelation($rows, $relationProperty, $findOptions),
        RelationType::MANY_TO_ONE => $this->loadManyToOneRelation($rows, $relationProperty, $findOptions),
        RelationType::MANY_TO_MANY => $this->loadManyToManyRelation($entityClass, $rows, $relationProperty, $findOptions),
        default => [],
      };
    }

    return $loadedRelations;
  }

  /**
   * @param object[] $rows
   * @return array<mixed, object>
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  private function loadManyToOneRelation(array $rows, RelationPropertyMetadata $relationProperty, FindOptions $findOptions): array
  {
    $targetClass = $relationProperty->getEntityClass();

    if (!$targetClass) {
      return [];
    }

    $foreignKeys = $this->extractScalarValues($rows, $relationProperty->name);
    if (empty($foreignKeys)) {
      return [];
    }

    $joinColumn = $relationProperty->joinColumn ?? $this->entityInspector->getJoinColumnAttribute($relationProperty->reflectionProperty->getDeclaringClass()->getName(), $relationProperty->name);
    $referenceColumn = $joinColumn->effectiveReferencedColumnName ?? 'id';
    $relatedRows = $this->fetchEntityRows(
      entityClass: $targetClass,
      conditionColumn: $referenceColumn,
      conditionValues: $foreignKeys,
      excludeColumns: $this->resolveRelationExcludeColumns($relationProperty, $findOptions)
    );

    $groupedRows = [];
    foreach ($relatedRows as $relatedRow) {
      $key = $relatedRow->{$referenceColumn} ?? null;
      if ($key !== null) {
        $groupedRows[$key] = $relatedRow;
      }
    }

    return $groupedRows;
  }

  /**
   * @param object[] $rows
   * @return array<mixed, object>
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  private function loadOneToOneRelation(string $entityClass, array $rows, RelationPropertyMetadata $relationProperty, FindOptions $findOptions): array
  {
    if ($relationProperty->joinColumn) {
      return $this->loadManyToOneRelation($rows, $relationProperty, $findOptions);
    }

    $targetClass = $relationProperty->getEntityClass();
    if (!$targetClass) {
      return [];
    }

    $inverseOwner = $this->resolveInverseOneToOneOwner($entityClass, $relationProperty);
    if (!$inverseOwner) {
      return [];
    }

    $localIds = $this->extractScalarValues($rows, 'id');
    if (empty($localIds)) {
      return [];
    }

    $inversePropertyName = $inverseOwner['property'];
    $joinColumn = $inverseOwner['joinColumn'];
    $relatedRows = $this->fetchEntityRows(
      entityClass: $targetClass,
      conditionColumn: $joinColumn->effectiveColumnName ?? $joinColumn->name ?? 'id',
      conditionValues: $localIds,
      excludeColumns: $this->resolveRelationExcludeColumns($relationProperty, $findOptions),
      relations: [$inversePropertyName]
    );

    $groupedRows = [];
    foreach ($relatedRows as $relatedRow) {
      $ownerKey = $relatedRow->{$inversePropertyName} ?? null;
      unset($relatedRow->{$inversePropertyName});

      if ($ownerKey !== null) {
        $groupedRows[$ownerKey] = $relatedRow;
      }
    }

    return $groupedRows;
  }

  /**
   * @param object[] $rows
   * @return array<mixed, object[]>
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  private function loadOneToManyRelation(array $rows, RelationPropertyMetadata $relationProperty, FindOptions $findOptions): array
  {
    $targetClass = $relationProperty->getEntityClass();
    $referenceProperty = $relationProperty->relationAttribute->referencedProperty ?? 'id';
    $inverseProperty = $relationProperty->relationAttribute->inverseSide ?? null;

    if (!$targetClass || !$inverseProperty) {
      return [];
    }

    $localKeys = $this->extractScalarValues($rows, $referenceProperty);
    if (empty($localKeys)) {
      return [];
    }

    $joinColumn = $this->entityInspector->getJoinColumnAttribute($targetClass, $inverseProperty);
    $relatedRows = $this->fetchEntityRows(
      entityClass: $targetClass,
      conditionColumn: $joinColumn->effectiveColumnName ?? $joinColumn->name ?? 'id',
      conditionValues: $localKeys,
      excludeColumns: $this->resolveRelationExcludeColumns($relationProperty, $findOptions),
      relations: [$inverseProperty]
    );

    $groupedRows = [];
    foreach ($relatedRows as $relatedRow) {
      $ownerKey = $relatedRow->{$inverseProperty} ?? null;
      unset($relatedRow->{$inverseProperty});

      if ($ownerKey === null) {
        continue;
      }

      $groupedRows[$ownerKey] ??= [];
      $groupedRows[$ownerKey][] = $relatedRow;
    }

    return $groupedRows;
  }

  /**
   * @param object[] $rows
   * @return array<mixed, object[]>
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  private function loadManyToManyRelation(string $entityClass, array $rows, RelationPropertyMetadata $relationProperty, FindOptions $findOptions): array
  {
    $mapping = $this->resolveManyToManyMapping($entityClass, $relationProperty);
    if (!$mapping) {
      return [];
    }

    $localIds = $this->extractScalarValues($rows, 'id');
    if (empty($localIds)) {
      return [];
    }

    $targetClass = $relationProperty->getEntityClass();
    if (!$targetClass) {
      return [];
    }

    $targetEntity = $this->create($targetClass);
    $targetTable = $this->entityInspector->getTableName($targetEntity);
    $joinTable = $mapping['joinTable'];
    $localJoinColumn = $mapping['localJoinColumn'];
    $targetJoinColumn = $mapping['targetJoinColumn'];
    $joinTableName = $joinTable->name ?? strtolower($mapping['ownerTable'] . '_' . $mapping['targetTable']);

    $columns = $this->entityInspector->getColumns(
      entity: $targetEntity,
      exclude: $this->resolveRelationExcludeColumns($relationProperty, $findOptions)
    );
    $columns['__relation_owner_key'] = "$joinTableName.$localJoinColumn";

    $query = new SQLQuery($this->query->getConnection());
    $statement = $query
      ->select()
      ->all(columns: $columns)
      ->from(tableReferences: $targetTable)
      ->join("`$joinTableName`")
      ->on("$joinTableName.$targetJoinColumn = $targetTable.id");
    $condition = $this->buildInCondition("$joinTableName.$localJoinColumn", $localIds, $query);
    if (!$condition) {
      return [];
    }

    $result = $statement
      ->where($condition)
      ->execute();

    if ($result->isError()) {
      throw new GeneralSQLQueryException($query);
    }

    $groupedRows = [];
    foreach ($result->getData() as $row) {
      $row = is_array($row) ? (object)$row : $row;
      $ownerKey = $row->__relation_owner_key ?? null;
      unset($row->__relation_owner_key);

      if ($ownerKey === null) {
        continue;
      }

      $groupedRows[$ownerKey] ??= [];
      $groupedRows[$ownerKey][] = $row;
    }

    return $groupedRows;
  }

  /**
   * @param string[] $excludeColumns
   * @param string[] $relations
   * @return object[]
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  private function fetchEntityRows(string $entityClass, string $conditionColumn, array $conditionValues, array $excludeColumns = ['password'], array $relations = []): array
  {
    if (empty($conditionValues)) {
      return [];
    }

    $entity = $this->create($entityClass);
    $columns = $this->entityInspector->getColumns(entity: $entity, exclude: $excludeColumns, relations: $relations);
    $tableName = $this->entityInspector->getTableName($entity);
    $query = new SQLQuery($this->query->getConnection());
    $statement = $query
      ->select()
      ->all(columns: $columns)
      ->from(tableReferences: $tableName);
    $condition = $this->buildInCondition("$tableName.$conditionColumn", $conditionValues, $query);

    if (!$condition) {
      return [];
    }

    $result = $statement
      ->where($condition)
      ->execute();

    if ($result->isError()) {
      throw new GeneralSQLQueryException($query);
    }

    return array_map(
      fn(object|array $row) => is_array($row) ? (object)$row : $row,
      $result->getData()
    );
  }

  private function resolveOneToOneRelationValue(object $entity, RelationPropertyMetadata $relationInfo, array $loadedRelations): ?object
  {
    $relationKey = $relationInfo->joinColumn
      ? ($entity->{$relationInfo->name} ?? null)
      : ($entity->id ?? null);

    if ($relationKey === null) {
      return null;
    }

    return $loadedRelations[$relationKey] ?? null;
  }

  private function resolveOneToManyRelationValue(object $entity, RelationPropertyMetadata $relationInfo, array $loadedRelations): array
  {
    $referenceProperty = $relationInfo->relationAttribute->referencedProperty ?? 'id';
    $referenceValue = $entity->{$referenceProperty} ?? null;

    if ($referenceValue === null) {
      return [];
    }

    return $loadedRelations[$referenceValue] ?? [];
  }

  private function resolveManyToOneRelationValue(object $entity, RelationPropertyMetadata $relationInfo, array $loadedRelations): ?object
  {
    $foreignKey = $entity->{$relationInfo->name} ?? null;

    if ($foreignKey === null) {
      return null;
    }

    return $loadedRelations[$foreignKey] ?? null;
  }

  private function resolveManyToManyRelationValue(object $entity, array $loadedRelations): array
  {
    $referenceValue = $entity->id ?? null;

    if ($referenceValue === null) {
      return [];
    }

    return $loadedRelations[$referenceValue] ?? [];
  }

  /**
   * @return array{property: string, joinColumn: JoinColumn}|null
   * @throws ReflectionException
   */
  private function resolveInverseOneToOneOwner(string $entityClass, RelationPropertyMetadata $relationProperty): ?array
  {
    $targetClass = $relationProperty->getEntityClass();
    if (!$targetClass) {
      return null;
    }

    $targetReflection = new ReflectionClass($targetClass);

    foreach ($targetReflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
      $oneToOneAttributes = $property->getAttributes(OneToOne::class);
      if (empty($oneToOneAttributes)) {
        continue;
      }

      /** @var OneToOne $attribute */
      $attribute = $oneToOneAttributes[0]->newInstance();
      if ($attribute->type !== $entityClass) {
        continue;
      }

      return [
        'property' => $property->getName(),
        'joinColumn' => $this->entityInspector->getJoinColumnAttribute($targetClass, $property->getName()),
      ];
    }

    return null;
  }

  /**
   * @return array{joinTable: JoinTable, ownerTable: string, targetTable: string, localJoinColumn: string, targetJoinColumn: string}|null
   * @throws ReflectionException
   * @throws ORMException
   * @throws ClassNotFoundException
   */
  private function resolveManyToManyMapping(string $entityClass, RelationPropertyMetadata $relationProperty): ?array
  {
    $targetClass = $relationProperty->getEntityClass();
    if (!$targetClass) {
      return null;
    }

    $ownerTable = $this->entityInspector->getTableName($this->create($entityClass));
    $targetTable = $this->entityInspector->getTableName($this->create($targetClass));
    $joinTable = $relationProperty->joinTable;

    if ($joinTable instanceof JoinTable) {
      return [
        'joinTable' => $joinTable,
        'ownerTable' => $ownerTable,
        'targetTable' => $targetTable,
        'localJoinColumn' => $joinTable->joinColumn ?? strtosingular($ownerTable) . '_id',
        'targetJoinColumn' => $joinTable->inverseJoinColumn ?? strtosingular($targetTable) . '_id',
      ];
    }

    $inverseProperty = $relationProperty->relationAttribute->inverseSide ?? null;
    $ownerProperty = $inverseProperty && property_exists($targetClass, $inverseProperty)
      ? new ReflectionProperty($targetClass, $inverseProperty)
      : $this->findManyToManyOwnerProperty($targetClass, $entityClass);

    if (!$ownerProperty) {
      return null;
    }

    $joinTableAttributes = $ownerProperty->getAttributes(JoinTable::class);
    if (empty($joinTableAttributes)) {
      return null;
    }

    /** @var JoinTable $joinTable */
    $joinTable = $joinTableAttributes[0]->newInstance();
    $inverseOwnerTable = $this->entityInspector->getTableName($this->create($targetClass));

    return [
      'joinTable' => $joinTable,
      'ownerTable' => $inverseOwnerTable,
      'targetTable' => $ownerTable,
      'localJoinColumn' => $joinTable->inverseJoinColumn ?? strtosingular($ownerTable) . '_id',
      'targetJoinColumn' => $joinTable->joinColumn ?? strtosingular($inverseOwnerTable) . '_id',
    ];
  }

  private function findManyToManyOwnerProperty(string $targetClass, string $entityClass): ?ReflectionProperty
  {
    $targetReflection = new ReflectionClass($targetClass);

    foreach ($targetReflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
      $relationAttributes = $property->getAttributes(\Assegai\Orm\Attributes\Relations\ManyToMany::class);
      $joinTableAttributes = $property->getAttributes(JoinTable::class);

      if (empty($relationAttributes) || empty($joinTableAttributes)) {
        continue;
      }

      /** @var \Assegai\Orm\Attributes\Relations\ManyToMany $attribute */
      $attribute = $relationAttributes[0]->newInstance();
      if ($attribute->type === $entityClass) {
        return $property;
      }
    }

    return null;
  }

  /**
   * @param object[] $rows
   * @return list<mixed>
   */
  private function extractScalarValues(array $rows, string $property): array
  {
    $values = [];

    foreach ($rows as $row) {
      $value = $row->{$property} ?? null;
      if ($value !== null && $value !== '') {
        $values[] = $value;
      }
    }

    return array_values(array_unique($values, SORT_REGULAR));
  }

  /**
   * @param list<mixed> $values
   */
  private function buildInCondition(string $qualifiedColumn, array $values, ?SQLQuery $query = null): ?string
  {
    if (empty($values)) {
      return null;
    }

    $query ??= $this->query;

    return SqlIdentifier::quote($qualifiedColumn) . ' IN (' . implode(', ', $query->addParams(array_values($values))) . ')';
  }

  private function resolveRelationExcludeColumns(RelationPropertyMetadata $relationProperty, FindOptions $findOptions): array
  {
    $relationOptions = $relationProperty->relationAttribute->options ?? null;

    if ($relationOptions instanceof RelationOptions) {
      return $relationOptions->exclude;
    }

    return $findOptions->exclude ?? ['password'];
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
  public function count(string $entityClass, ?FindOptions $options = null): int
  {
    $entity = $this->create(entityClass: $entityClass);

    $statement = $this->query->select()->count()->from(tableReferences: $this->entityInspector->getTableName(entity: $entity));

    if ($options) {
      $conditions = [];

      if ($deleteColumnName = $this->getDeleteDateColumnName(entityClass: $entityClass)) {
        $conditions = array_merge($options->where->conditions ?? $options->where ?? [], [$deleteColumnName => 'NULL']);
      }

      $statement = $statement->where(condition: new FindWhereOptions(conditions: $conditions, entityClass: $entityClass));
    }

    if ($this->isDebug || $options?->isDebug) {
      $statement->debug();
    }

    $result = $statement->execute();

    if ($result->isError()) {
      throw new GeneralSQLQueryException($this->query);
    }

    return $result->value()[0]?->total ?? 0;
  }

  /**
   * Returns the id of the last inserted entity.
   *
   * @return int|null Returns the id of the last inserted entity.
   */
  public function lastInsertId(): ?int
  {
    return $this->query->lastInsertId();
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
    if (is_array($where)) {
      $where = new FindWhereOptions(
        conditions: $where['condition'] ?? $where,
        entityClass: $entityClass
      );
    }
    $statement = $this->query->select()->all(columns: $this->entityInspector->getColumns(entity: $entity, exclude: $where->exclude))->from(tableReferences: $this->entityInspector->getTableName(entity: $entity))->where(condition: $where);

    $limit = $_GET['limit'] ?? 100;
    $skip = $_GET['skip'] ?? 0;

    $statement = $statement->limit(limit: $limit, offset: $skip);

    if ($this->isDebug) {
      $statement->debug();
    }

    $result = $statement->execute();

    if ($result->isError()) {
      throw new GeneralSQLQueryException($this->query);
    }

    return new FindResult(raw: $result->getRaw(), data: $result->getData(), errors: $result->getErrors());
  }

  /**
   * Updates entity partially. Entity can be found by a given condition(s).
   * Unlike save method executes a primitive operation without cascades, relations and other operations included.
   * Executes fast and efficient UPDATE query.
   * Does not check if entity exist in the database.
   * Condition(s) cannot be empty.
   *
   * @param string $entityClass The entity class name.
   * @param object|array $partialEntity The entity or entities to update.
   * @param string|object|array $conditions The condition(s) to find the entity.
   * @param UpdateOptions|null $options The options to use when updating the entity.
   * @return UpdateResult
   * @throws ClassNotFoundException
   * @throws EmptyCriteriaException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   * @throws ValidationException
   */
  public function update(string $entityClass, object|array $partialEntity, string|object|array $conditions, ?UpdateOptions $options = null): UpdateResult
  {
    $this->validateConditions(conditions: $conditions, methodName: __METHOD__);

    if (empty($conditions)) {
      throw new ORMException("Empty criteria(s) are not allowed for the update method.");
    }

    if (is_array($partialEntity)) {
      $raw = '';
      $affected = 0;
      $generatedMaps = new stdClass();

      foreach ($partialEntity as $partialItem) {
        $result = $this->update(entityClass: $entityClass, partialEntity: $partialItem, conditions: $conditions);
        $generatedMaps = $result->generatedMaps;
      }

      return new UpdateResult(raw: $this->query->queryString(), affected: $this->query->rowCount(), identifiers: (object)$partialEntity, generatedMaps: $generatedMaps);
    }

    $entityInstance = $this->create(entityClass: $entityClass, entityLike: $partialEntity);
    $assignmentList = [];
    $columnOptions = [];
    $relations = [];
    $relationProperties = [];

    if ($options?->relations) {
      $relations = is_object($options->relations) ? (array)$options->relations : $options->relations;
    }

    $columnMap = $this->entityInspector->getColumns(entity: $entityInstance, exclude: $options->readonlyColumns ?? $this->readonlyColumns, relations: $relations, relationProperties: $relationProperties, meta: $columnOptions);

    foreach ($partialEntity as $prop => $value) {
      # Get the correct prop name
      $columnName = $this->getColumnNameFromProperty($entityInstance, $prop);

      if ($this->mapContainsColumnName($columnMap, $columnName)) {
        if (!is_null($value)) {
          if ($value instanceof UnitEnum && property_exists($value, 'value')) {
            $value = $value->value;
          }

          if ($value instanceof DateTime) {
            $value = $this->entityInspector->convertDateTimeToString($value, $prop, $columnOptions);
          }

          if ($value instanceof stdClass) {
            $value = json_encode($value);
          }

          if (isset($columnMap[$columnName])) {
            if (isset($relationProperties[$columnName])) {
              assert($relationProperties[$columnName] instanceof RelationPropertyMetadata);

              if (is_object($value) && property_exists($value, $relationProperties[$columnName]->joinColumn->effectiveReferencedColumnName)) {
                $value = $value->{$relationProperties[$columnName]->joinColumn->effectiveReferencedColumnName};
              }

              $columnName = $relationProperties[$columnName]->joinColumn->effectiveColumnName;
            } else {
              $columnName = $columnMap[$columnName];
            }
          }

          $assignmentList[$columnName] = $value;
        }
      }
    }

    if (empty($assignmentList)) {
      return new UpdateResult(null, 0, $partialEntity, new stdClass());
    }

    $statement = $this->query
      ->update(tableName: $this->entityInspector->getTableName(entity: $entityInstance))
      ->set(assignmentList: $assignmentList);
    $conditionString = is_string($conditions)
      ? $conditions
      : $this->buildConditionClause($conditions, $this->query, $entityInstance);
    $statement->where(condition: $conditionString);

    if ($this->isDebug || $options?->isDebug) {
      $this->query->debug();
    }

    $result = $this->query->execute();

    if ($result->isError()) {
      throw new GeneralSQLQueryException($this->query);
    }

    $updatedEntity = $this->findOne(entityClass: $entityClass, options: new FindOptions(where: $conditions));
    $generatedMaps = $updatedEntity->getData() ?? new stdClass();

    return new UpdateResult(raw: $this->query->queryString(), affected: $this->query->rowCount(), identifiers: $partialEntity, generatedMaps: $generatedMaps);
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
    if (empty($conditions)) {
      throw new EmptyCriteriaException(methodName: $methodName);
    }
  }

  /**
   * @param int|object|array $conditions
   * @param SQLQuery $query
   * @param object|null $entity
   * @return string
   */
  private function buildConditionClause(int|object|array $conditions, SQLQuery $query, ?object $entity = null): string
  {
    if (is_int($conditions)) {
      return SqlIdentifier::quote('id') . '=' . $query->addParam($conditions);
    }

    $parts = [];

    foreach ((array)$conditions as $key => $value) {
      $columnName = $entity && is_string($key)
        ? $this->getColumnNameFromProperty($entity, $key)
        : (string)$key;
      $identifier = SqlIdentifier::quote($columnName);

      if (is_null($value) || $value === 'NULL') {
        $parts[] = "$identifier IS NULL";
        continue;
      }

      if (is_array($value) && array_is_list($value)) {
        if (empty($value)) {
          $parts[] = '1 = 0';
          continue;
        }

        $parts[] = $identifier . ' IN (' . implode(', ', $query->addParams($value)) . ')';
        continue;
      }

      $parts[] = $identifier . '=' . $query->addParam($value);
    }

    return implode(' AND ', $parts);
  }

  /**
   * Returns the column name for a given property. If no column name is specified, returns the property name.
   *
   * @param object $entity The entity to get the column name for.
   * @param string $prop The property to get the column name for.
   * @return string Returns the column name for the given property.
   */
  private function getColumnNameFromProperty(object $entity, string $prop): string
  {
    if (!property_exists($entity, $prop)) {
      # Check if this is a relation property

      # Get all entities with
      return $prop;
    }
    $propertyReflection = new ReflectionProperty($entity, $prop);
    $attributes = $propertyReflection->getAttributes();

    if (empty($attributes)) {
      return $prop;
    }

    # Return $prop if no valid Column attribute found
    $columnClassNames = [Column::class, CreateDateColumn::class, DeleteDateColumn::class, EmailColumn::class, PasswordColumn::class, PrimaryGeneratedColumn::class, UpdateDateColumn::class, URLColumn::class];
    $columnAttribute = null;
    foreach ($attributes as $attribute) {
      if (in_array($attribute->getName(), $columnClassNames)) {
        $columnAttribute = $attribute;
      }
    }

    if (!$columnAttribute) {
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
    foreach ($columnMap as $key => $value) {
      if (str_ends_with($key, $columnName)) {
        return true;
      }

      if (str_ends_with($value, $columnName)) {
        return true;
      }
    }
    return false;
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

    foreach ($entityLike as $prop => $value) {
      if (property_exists($entityClass, $prop)) {
        $findOptions[$prop] = $value;
      }
    }

    $entity = $this->find(entityClass: $entityClass, findOptions: FindOptions::fromArray($findOptions));

    if (empty($entity->getData())) {
      $entity = $this->create(entityClass: $entityClass, entityLike: $entityLike);
    }

    return $this->merge($entityClass, $entity, $entityLike);
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

    if ($entity instanceof Entity) {
      $object = $entity;

      foreach ($entities as $item) {
        if (is_object($item) || is_array($item)) {
          $object = (object)array_merge((array)$object, (array)$item);
        }
      }

      foreach ($object as $prop => $value) {
        if (property_exists($entity, $prop)) {
          $entity->$prop = $value;
        }
      }
    }

    return $entity;
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
  public function upsert(string $entityClass, object|array $entityOrEntities, array|UpsertOptions $options): InsertResult|UpdateResult
  {
    // TODO: #83 Implement EntityManager::upsert @amasiye
    if (is_array($options)) {
      $options = array_is_list($options)
        ? new UpsertOptions(conflictPaths: $options)
        : UpsertOptions::fromArray($options);
    }

    if (is_array($entityOrEntities)) {
      $results = [];
      $errors = [];
      foreach ($entityOrEntities as $entity) {
        $result = $this->upsert(entityClass: $entityClass, entityOrEntities: $entity, options: $options);

        if ($result->isError()) {
          $errors[] = $result->getErrors();
        }

        $results[] = $result->getData();
      }

      $generatedMaps = new stdClass();
      $generatedMaps->results = $results;

      return new UpdateResult(raw: $this->query->queryString(), affected: $this->query->rowCount(), identifiers: $entityOrEntities, generatedMaps: $generatedMaps, errors: $errors);
    }

    $this->validateEntityName(entityClass: $entityClass);

    // TODO: Configure the upsert options

    $columns = $this->entityInspector->getColumns(entity: $entityOrEntities);
    $updateColumns = $this->entityInspector->getColumns(entity: $entityOrEntities, exclude: $options->readonlyColumns ?? $this->readonlyColumns);
    $values = $this->entityInspector->getValues(entity: $entityOrEntities);
    $tableName = $this->entityInspector->getTableName(entity: $entityOrEntities);

    if ($this->connection->type === DataSourceType::SQLITE) {
      return $this->executeSqliteUpsert($tableName, $entityOrEntities, $columns, $updateColumns, $values, $options);
    }

    $assignmentList = array_map(function($column): string {
      $column = $this->stripTableName($column);
      return "$column=VALUES($column)";
    }, array_values($updateColumns));

    $this->query->insertInto(tableName: $tableName)->singleRow(columns: $columns)->values(valuesList: $values)->onDuplicateKeyUpdate(assignmentList: $assignmentList);

    if ($this->isDebug) {
      $this->query->debug();
    }

    $result = $this->query->execute();

    $errors = [];
    if ($result->isError()) {
      $errors[] = $this->query->getConnection()->errorInfo();
      $errors[] = new GeneralSQLQueryException($this->query);
    }

    $generatedMaps = $entityOrEntities;

    if ($result->isOk()) {
      $generatedMaps->id = $this->lastInsertId();
    }

    return new InsertResult(identifiers: (object)['id' => $this->lastInsertId()], raw: $this->query->queryString(), generatedMaps: $entityOrEntities, errors: $errors);
  }

  private function executeSqliteUpsert(
    string $tableName,
    object $entity,
    array $columns,
    array $updateColumns,
    array $values,
    UpsertOptions $options
  ): InsertResult
  {
    $originalId = $entity->id ?? null;
    $columns = array_map([$this, 'stripTableName'], array_values($columns));
    $updateColumns = array_map([$this, 'stripTableName'], array_values($updateColumns));
    $conflictPaths = array_map([$this, 'stripTableName'], $options->conflictPaths ?: ['id']);

    $filteredColumns = [];
    $filteredValues = [];

    foreach ($columns as $index => $column) {
      $value = $values[$index] ?? null;

      if ($value === null && !in_array($column, $conflictPaths, true) && !in_array($column, $updateColumns, true)) {
        continue;
      }

      $filteredColumns[] = $column;
      $filteredValues[] = $value;
    }

    $columns = $filteredColumns;
    $values = $filteredValues;

    $quotedColumns = implode(', ', array_map(fn(string $column): string => SqlIdentifier::quote($column), $columns));
    $quotedConflictPaths = implode(', ', array_map(fn(string $column): string => SqlIdentifier::quote($column), $conflictPaths));
    $placeholders = array_fill(0, count($values), '?');
    $valueList = implode(', ', $placeholders);
    $assignmentList = implode(', ', array_map(fn(string $column): string => SqlIdentifier::quote($column) . '=excluded.' . SqlIdentifier::quote($column), $updateColumns));
    $conflictAction = empty($assignmentList) ? 'DO NOTHING' : "DO UPDATE SET $assignmentList";

    $queryString = "INSERT INTO `$tableName` ($quotedColumns) VALUES ($valueList) ON CONFLICT ($quotedConflictPaths) $conflictAction";
    $this->query->init();
    $this->query->insertInto(tableName: $tableName);
    $this->query->setQueryString($queryString);
    $this->query->addParams($values);

    if ($this->isDebug) {
      $this->query->debug();
    }

    $result = $this->query->execute();
    $errors = [];

    if ($result->isError()) {
      $errors[] = $this->query->getConnection()->errorInfo();
      $errors[] = new GeneralSQLQueryException($this->query);
    }

    if ($result->isOk()) {
      $lastInsertId = $this->lastInsertId();
      if (empty($originalId) && $lastInsertId) {
        $entity->id = $lastInsertId;
      }
    }

    $identifier = $entity->id ?? $originalId ?? $this->lastInsertId();

    return new InsertResult(
      identifiers: (object)['id' => $identifier],
      raw: $this->query->queryString(),
      generatedMaps: $entity,
      errors: $errors,
      affected: $this->query->rowCount() ?? 0,
    );
  }

  private function stripTableName(string $column): string
  {
    $parts = explode('.', $column);
    return end($parts);
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
  public function remove(object|array $entityOrEntities, ?RemoveOptions $removeOptions = null): DeleteResult
  {
    if (is_object($entityOrEntities)) {
      $id = $entityOrEntities->id ?? 0;
      $statement = $this->query
        ->deleteFrom(tableName: $this->entityInspector->getTableName(entity: $entityOrEntities));
      $condition = $this->buildConditionClause(['id' => $id], $this->query, $entityOrEntities);
      $statement = $statement->where($condition);

      if ($this->isDebug) {
        $statement->debug();
      }

      $result = $statement->execute();

      if ($result->isError()) {
        throw new GeneralSQLQueryException($this->query);
      }

      return new DeleteResult(raw: $this->query->queryString(), affected: $result->getTotalAffectedRows());
    }

    $affected = 0;
    $raw = '';
    foreach ($entityOrEntities as $entity) {
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
   * @param string $primaryKeyField
   * @return UpdateResult Returns the removed entities.
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws DateInvalidTimeZoneException
   * @throws DateMalformedStringException
   */
  public function softRemove(
    object|array $entityOrEntities,
    RemoveOptions|array|null $removeOptions = null,
    string $primaryKeyField = 'id'
  ): UpdateResult
  {
    $result = null;
    $timezone = getenv('TIMEZONE') ?: self::DEFAULT_TIMEZONE;
    $deletedAtFormat = getenv('DELETED_AT_FORMAT') ?: self::DEFAULT_DELETED_AT_FORMAT;
    $deletedAt = new DateTimeImmutable('now', new DateTimeZone($timezone));
    $deletedAt = $deletedAt->format($deletedAtFormat);
    $primaryColumn = $this->getPrimaryKeyColumnName($entityOrEntities, $primaryKeyField);

    if (is_object($entityOrEntities)) {
      if (!$entityOrEntities->{$primaryKeyField}) {
        throw new ORMException("Entity must have an '$primaryKeyField' field to be soft removed.");
      }

      $primaryKeyFieldValue = $entityOrEntities->{$primaryKeyField};
      $statement = $this->query
        ->update(tableName: $this->entityInspector->getTableName(entity: $entityOrEntities))
        ->set([Filter::getDeleteDateColumnName(entity: $entityOrEntities) => $deletedAt]);
      $condition = $this->buildConditionClause([$primaryColumn => $primaryKeyFieldValue], $this->query, $entityOrEntities);
      $statement = $statement->where($condition);

      if ($this->isDebug || $removeOptions?->isDebug) {
        $statement->debug();
      }

      $result = $statement->execute();

      if ($result->isError()) {
        throw new GeneralSQLQueryException($this->query);
      }

      // TODO: #88 Verify that delete occurred @amasiye
      $generatedMaps = new stdClass();
      foreach ($result->value() as $key => $value) {
        $generatedMaps->$key = $value;
      }

      return new UpdateResult(raw: $this->query->queryString(), affected: $this->query->rowCount(), identifiers: (object)$entityOrEntities, generatedMaps: $generatedMaps);
    }

    $identifiers = new stdClass();
    $generatedMaps = new stdClass();

    $numberFormatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
    foreach ($entityOrEntities as $id => $entity) {
      $key = is_numeric($id) ? $numberFormatter->format($id) : $id;
      $result = $this->softRemove(entityOrEntities: $entity, removeOptions: $removeOptions);
      $identifiers->$key = $result;
      $generatedMaps->$key = $result->generatedMaps;
    }

    return new UpdateResult(raw: $result, affected: $this->query->rowCount(), identifiers: $identifiers, generatedMaps: $generatedMaps);
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
  public function delete(string $entityClass, int|array|object $conditions): DeleteResult
  {
    $this->validateConditions(conditions: $conditions, methodName: __METHOD__);

    $entity = $this->create(entityClass: $entityClass);

    $statement = $this->query
      ->deleteFrom(tableName: $this->entityInspector->getTableName(entity: $entity));
    $statement = $statement->where(condition: $this->buildConditionClause($conditions, $this->query, $entity));

    if ($this->isDebug) {
      $statement->debug();
    }

    $deletionResult = $statement->execute();

    if ($deletionResult->isError()) {
      throw new GeneralSQLQueryException($this->query);
    }

    return new DeleteResult(raw: $deletionResult->value(), affected: $this->query->rowCount());
  }

  /**
   * @param int|object|array $conditions
   *
   * @return string Returns an SQL condition string
   */
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
  public function restore(string $entityClass, int|array|object $conditions): UpdateResult
  {
    $entity = $this->create(entityClass: $entityClass);

    $statement = $this->query
      ->update(tableName: $this->entityInspector->getTableName(entity: $entity))
      ->set([Filter::getDeleteDateColumnName(entity: $entity) => NULL]);
    $statement = $statement->where(condition: $this->buildConditionClause($conditions, $this->query, $entity));

    if ($this->isDebug) {
      $statement->debug();
    }

    $restoreResult = $statement->execute();

    if ($restoreResult->isError()) {
      throw new GeneralSQLQueryException($this->query);
    }

    $generatedMaps = new stdClass();

    foreach ($restoreResult->value() as $key => $value) {
      $generatedMaps->$key = $value;
    }

    return new UpdateResult(raw: $restoreResult->value(), affected: $this->query->rowCount(), identifiers: $entity, generatedMaps: $generatedMaps);
  }

  /**
   * Finds entities that match given find options.
   * Also counts all entities that match given conditions,
   * but ignores pagination settings (from and take options).
   *
   * @param string $entityClass
   * @param FindManyOptions|null $options
   * @return FindResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  #[ArrayShape(['entities' => "array|null", 'count' => "int"])]
  public function findAndCount(string $entityClass, ?FindManyOptions $options = null): FindResult
  {
    $result = $this->find(entityClass: $entityClass, findOptions: $options);

    return new FindResult($result->getRaw(), $result->getTotal(), $result->getErrors(), $result->getTotalAffectedRows());
  }

  /**
   * Finds entities that match given WHERE conditions.
   * Also counts all entities that match given conditions,
   * but ignores pagination settings (from and take options).
   * @param string $entityClass
   * @param FindWhereOptions|array $where
   * @return FindResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function findAndCountBy(string $entityClass, FindWhereOptions|array $where): FindResult
  {
    $result = $this->findBy(entityClass: $entityClass, where: $where);

    return new FindResult($result->getRaw(), $result->getTotal(), $result->getErrors(), $result->getTotalAffectedRows());
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

    foreach ($object as $prop => $value) {
      if (property_exists($entity, $prop)) {
        $sourceReflection = new ReflectionProperty($object, $prop);
        $targetReflection = new ReflectionProperty($entity, $prop);
        $sourceReflectionType = $sourceReflection->getType();
        $targetReflectionType = $targetReflection->getType();

        if ($sourceReflectionType instanceof ReflectionUnionType) {
          $this->logger->warning("Source instance of ReflectionUnionType");
          $sourceType = strval($sourceReflectionType);
          if ($sourceReflectionType->allowsNull()) {
            $sourceType = "null|$sourceType";
          }
        } else {
          $sourceType = $sourceReflectionType?->getName() ?? match (gettype($object->$prop)) {
            'integer' => 'int',
            'double'  => 'float',
            'boolean' => 'bool',
            'NULL'    => null,
            'object'  => get_class($object->$prop),
            default   => gettype($object->$prop)
          };
        }

        if ($targetReflectionType instanceof ReflectionUnionType) {
          $this->logger->warning("Target instance of ReflectionUnionType");
          $targetType = strval($targetReflectionType);
          if ($targetReflectionType->allowsNull()) {
            $targetType = "null|$targetType";
          }
        } else {
          $targetType = $targetReflectionType?->getName() ?? match (gettype($entity->$prop)) {
            'integer' => 'int',
            'double'  => 'float',
            'boolean' => 'bool',
            'NULL'    => null,
            'object'  => get_class($object->$prop),
            default   => gettype($entity->$prop)
          };
        }

        if (is_null($sourceType) || is_null($targetType)) {
          continue;
        }

        if ($sourceType !== $targetType && !str_contains($targetType, $sourceType)) {
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
   * @return bool
   */
  public function hasStoreEntry(string $key): bool
  {
    return isset($this->entities[$key]);
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
   * Gets the primary key column name for a given entity class.
   *
   * @param object $entity
   * @param string $primaryKeyField
   * @return string
   * @throws ClassNotFoundException
   * @throws ORMException
   */
  private function getPrimaryKeyColumnName(object $entity, string $primaryKeyField = 'id'): string
  {
    $this->entityInspector->validateEntityName($entity::class);

    $primaryColumn = null;
    $columns = $this->entityInspector->getColumns(entity: $entity);
    foreach ($columns as $alias => $column) {
      if ($alias === $primaryKeyField || $column === $primaryKeyField) {
        $primaryColumn = $column;
        break;
      }
    }

    if (!$primaryColumn) {
      throw new ORMException("Entity " . $entity::class . " does not have a primary key column.");
    }

    return $primaryColumn;
  }
}
