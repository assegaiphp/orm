<?php

namespace Assegai\Orm\Management\Inspectors;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Attributes\Relations\JoinColumn;
use Assegai\Orm\Attributes\Relations\JoinTable;
use Assegai\Orm\Attributes\Relations\ManyToMany;
use Assegai\Orm\Attributes\Relations\ManyToOne;
use Assegai\Orm\Attributes\Relations\OneToMany;
use Assegai\Orm\Attributes\Relations\OneToOne;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Metadata\RelationPropertyMetadata;
use Assegai\Orm\Queries\Sql\ColumnType;
use DateTime;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

/**
 * Provides for entity object introspection.
 */
final class EntityInspector
{
  /**
   * @var EntityInspector|null The singleton instance of the EntityInspector.
   */
  private static ?EntityInspector $instance = null;

  /**
   * Constructs a new EntityInspector
   */
  private final function __construct()
  {
  }

  /**
   * Returns the singleton instance of the EntityInspector.
   *
   * @return EntityInspector The singleton instance of the EntityInspector.
   */
  public static function getInstance(): EntityInspector
  {
    if (!self::$instance)
    {
      self::$instance = new EntityInspector();
    }

    return self::$instance;
  }

  /**
   * Asserts that the specified class name is a valid entity and throws an exception if it is not.
   *
   * @param string $entityClass The name of the class to validate.
   * @return void
   * @throws ClassNotFoundException If the class does not exist.
   * @throws ORMException If the class does not have the required attributes.
   */
  public function validateEntityName(string $entityClass): void
  {
    if (!class_exists($entityClass))
    {
      throw new ClassNotFoundException(className: $entityClass);
    }

    $reflectionClass = new ReflectionClass($entityClass);
    $entityAttribute = $reflectionClass->getAttributes(Entity::class);

    if (empty($entityAttribute))
    {
      throw new ORMException(message: "Missing Entity attribute for $entityClass");
    }
  }

  /**
   * Returns the metadata for the specified entity.
   *
   * @param object $entity The entity to inspect.
   * @return Entity The metadata for the entity.
   * @throws ClassNotFoundException If the entity does not have the required attributes.
   * @throws ORMException If the entity attributes have invalid values.
   * @throws ReflectionException If the entity cannot be reflected.
   */
  public function getMetaData(object $entity): Entity
  {
    $this->validateEntityName(get_class($entity));
    $className = get_class($entity);

    $entityReflection = new ReflectionClass($className);
    $entityAttributesReflections = $entityReflection->getAttributes(Entity::class);

    if (empty($entityAttributesReflections))
    {
      throw new ORMException("Entity attribute not found on class $className.");
    }

    $entityAttributeReflection = $entityAttributesReflections[0];
    /** @var Entity $entityAttributeInstance */
    $entityAttributeInstance = $entityAttributeReflection->newInstance();

    return $entityAttributeInstance;
  }

  /**
   * Returns a list of class property names that are marked with the `Column` attribute.
   *
   * @param object $entity The entity to inspect.
   * @param string[] $exclude A list of properties to exclude.
   * @param string[] $relations A list of properties that are marked with the `JoinColumn` attribute.
   * @param array<string, mixed> $relationProperties A list of properties that are marked with the `JoinColumn` attribute.
   * @param array<string, mixed> $meta The metadata for the entity.
   * @return array Returns a list of properties that are marked with the `Column` attribute.
   * @throws ClassNotFoundException
   * @throws ORMException
   */
  public function getColumns(
    object $entity,
    array $exclude = [],
    array $relations = [],
    array &$relationProperties = [],
    array &$meta = []
  ): array
  {
    $columns = [];
    $reflectionClass = new ReflectionClass($entity);
    $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

    $tableName = $this->getTableName($entity);

    foreach ($properties as $property) {
      if (in_array($property->getName(), $exclude)) {
        continue;
      }

      $propertyName = $property->getName();
      $attributes = $property->getAttributes();
      foreach ($attributes as $attribute) {
        $attributeInstance = $attribute->newInstance();

        if ($attributeInstance instanceof Column) {

          if ($attributeInstance->alias) {
            $columns[$attributeInstance->alias] = "$tableName.$attributeInstance->name";
          } else if($attributeInstance->name) {
            $columns[$propertyName] = "$tableName.$attributeInstance->name";
          } else {
            $columns[] = "$tableName.$propertyName";
          }

          # Set the ColumnType
          $meta['columnTypes'][$propertyName] = $attributeInstance->type;
        }

        if ($relations) {
          if ($attributeInstance instanceof JoinColumn) {

            if ($attributeInstance->name) {
              $columns[$propertyName] = "$tableName." . $attributeInstance->name;
            } else {
              $attributeInstance->effectiveColumnName = $this->getColumnName([$propertyName, 'Id']);
              $columns[] = "$tableName." . $attributeInstance->effectiveColumnName;
            }

            if (!$relationProperties[$propertyName]) {
              $relationProperties[$propertyName] = new RelationPropertyMetadata(reflectionProperty: $property);
            }

            $relationProperties[$propertyName]->joinColumn = $attributeInstance;
          } else if ($attributeInstance instanceof JoinTable) {
            if (!$relationProperties[$propertyName]) {
              $relationProperties[$propertyName] = new RelationPropertyMetadata(reflectionProperty: $property);
            }

            $relationProperties[$propertyName]->joinTable = $attributeInstance;
          } else if ($attributeInstance instanceof OneToOne) {
            if (!isset($relationProperties[$propertyName]) || !$relationProperties[$propertyName])
            {
              $relationProperties[$propertyName] = new RelationPropertyMetadata(reflectionProperty: $property);
            }

            $relationProperties[$propertyName]->relationAttribute = $attributeInstance;
            $relationProperties[$propertyName]->relationAttributeReflection = $attribute;
            $relationProperties[$propertyName]->inflate();

            # Instantiate relative
            $entityRelative = new $attributeInstance->type;

            # Get relative columns
            $entityRelativeColumns = $this->getRelationColumns(entity: $entityRelative, exclude: $exclude);

            # Add relative columns to column list
            $columns = array_merge($columns, $entityRelativeColumns);
          } else if ($attributeInstance instanceof OneToMany) {
            if (!isset($relationProperties[$propertyName])) {
              $relationProperties[$propertyName] = new RelationPropertyMetadata(reflectionProperty: $property);
            }

            $relationProperties[$propertyName]->relationAttribute = $attributeInstance;
            $relationProperties[$propertyName]->relationAttributeReflection = $attribute;
            $relationProperties[$propertyName]->inflate();
          } else if ($attributeInstance instanceof ManyToOne) {
            if (!isset($relationProperties[$propertyName])) {
              $relationProperties[$propertyName] = new RelationPropertyMetadata(reflectionProperty: $property);
            }

            $relationProperties[$propertyName]->relationAttribute = $attributeInstance;
            $relationProperties[$propertyName]->relationAttributeReflection = $attribute;
            $relationProperties[$propertyName]->inflate();
          } else if ($attributeInstance instanceof ManyToMany) {
            if (!isset($relationProperties[$propertyName])) {
              $relationProperties[$propertyName] = new RelationPropertyMetadata(reflectionProperty: $property);
            }

            $relationProperties[$propertyName]->relationAttribute = $attributeInstance;
            $relationProperties[$propertyName]->relationAttributeReflection = $attribute;
            $relationProperties[$propertyName]->inflate();
          }
        }
      }
    }

    return $columns;
  }

  /**
   * Returns the columns for the specified entity.
   *
   * @param object $entity The entity to inspect.
   * @param array $exclude A list of properties to exclude.
   * @return array<string, string> Returns the columns for the specified entity.
   */
  private function getRelationColumns(object $entity, array $exclude = []): array
  {
    $columns = [];
    $reflectionClass = new ReflectionClass($entity);
    $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

    try {
      $tableName = $this->getTableName($entity);
      foreach ($properties as $property) {
        $propertyName = $property->getName();
        $columnAttributes = $property->getAttributes(Column::class);

        if (!$columnAttributes || in_array($propertyName, $exclude)) {
          continue;
        }

        foreach ($columnAttributes as $columnAttribute) {
          $attributeInstance = $columnAttribute->newInstance();
          if ($attributeInstance instanceof Column) {
            if ($attributeInstance->alias) {
              $columns["{$tableName}_" . $attributeInstance->alias] = "$tableName." . $attributeInstance->name;
            } else if($attributeInstance->name) {
              $columns[$propertyName] = "$tableName." . $attributeInstance->name;
            } else {
              $columns["{$tableName}_" . $propertyName] = "$tableName." . $propertyName;
            }
          }
        }
      }
    }
    catch (ClassNotFoundException|ORMException $e)
    {
      die($e);
    }

    return $columns;
  }

  /**
   * Returns the values of the specified entity.
   *
   * @param object $entity The entity to inspect.
   * @param string[] $exclude A list of properties to exclude.
   * @param array<string, mixed> $options The options to use when retrieving the values.
   * @return array<int, mixed> Returns the values of the specified entity.
   * @throws ClassNotFoundException If the entity does not have the required attributes.
   * @throws ORMException If the entity attributes have invalid values.
   * @throws ReflectionException If the entity cannot be reflected.
   */
  public function getValues(object $entity, array $exclude = [], array $options = ['filter' => true]): array
  {
    $filterValues = $options['filter'] ?? true;
    $values = [];
    $entityClassname = get_class($entity);
    $this->validateEntityName($entityClassname);
    $columns = $this->getColumns(entity: $entity, exclude: $exclude);

    foreach ($columns as $index => $column) {
      $propName = is_numeric($index) ? $column : $index;
      $propName = str_replace($this->getTableName($entity) . ".", '', $propName);
      $property = $entity->$propName;

      if (empty($property)) {
        $columnAttribute = new ReflectionProperty(get_class($entity), $propName);
        $attributes = $columnAttribute->getAttributes();

        foreach ($attributes as $attribute) {
          $attrInstance = $attribute->newInstance();
          if (!empty($attrInstance->defaultValue)) {
            $property = $attrInstance->defaultValue;
          }
        }
      }

      // TODO: Perform type conversion
      if (is_object($property)) {
        if (property_exists($property, 'value')) {
          $property = $property->value;
        }

        if ($property instanceof DateTime) {
          $property = $this->convertDateTimeToString($property, $propName, $options);
        }

        if ($property instanceof stdClass) {
          $property = json_encode($property);
        }
      }
      $filteredValue = match(gettype($entity->$propName)) {
        'integer' => filter_var($property, FILTER_SANITIZE_NUMBER_INT),
        'double' => filter_var($property, FILTER_SANITIZE_NUMBER_FLOAT),
        'boolean' => boolval($property),
        'string' => filter_var($property, FILTER_SANITIZE_ADD_SLASHES),
        default => $property
      };
      $values[] = ($filterValues) ? $filteredValue : $property;
    }

    return $values;
  }

  /**
   * Returns the table name for the specified entity.
   *
   * @param object $entity The entity to inspect.
   * @return string The name of the table associated with the entity.
   * @throws ClassNotFoundException If the entity does not have the required attributes.
   * @throws ORMException If the entity attributes have invalid values.
   */
  public function getTableName(object $entity): string
  {
    $tableName = '';

    $this->validateEntityName(get_class($entity));
    $reflectionClass = new ReflectionClass($entity);
    $attributes = $reflectionClass->getAttributes(Entity::class);

    foreach ($attributes as $attribute)
    {
      $arguments = $attribute->getArguments();
      $tableName = $arguments['table'] ?? $this->getTableNameFromClassName(get_class($entity));
    }

    return $tableName;
  }

  /**
   * Returns the database table name associated with a given class name.
   * The `getTableNameFromClassName` method retrieves the database table name associated with a given class name.
   * It takes a `String` parameter `className` that represents the name of the class for which the table name should
   * be retrieved. The method returns a `String` representing the name of the database table associated with the given
   * class name.
   * If the `className` parameter is empty or `null`, the method throws an `IllegalArgumentException`.
   *
   * @param string $className The name of the class for which to retrieve the associated table name
   * @return string Returns the name of the database table associated with the class name.
   * @throws ORMException If the given class name is empty or null.
   */
  private function getTableNameFromClassName(string $className): string
  {
    if (empty($className))
    {
      throw new ORMException("Class name cannot be empty.");
    }
    $tokens = explode('\\', $className);
    $className = array_pop($tokens);
    return strtolower(str_replace('Entity', '', $className));
  }

  /**
   * @param string $name
   * @return string
   */
  private function getColumnName(string|array $name): string
  {
    $output = $name;
    if (is_array($output))
    {
      $output = implode(' ', $output);
    }
    $output = strtolower($output);
    $output = ucwords(preg_replace('/[\W+]/', ' ', $output));
    $output = str_replace(' ', '', $output);
    return lcfirst($output);
  }

  /**
   * Checks if the specified entity has a valid structure.
   *
   * @param object|array $entity The entity to check.
   * @param string $entityClass The name of the entity class.
   * @return bool Returns `true` if the entity has a valid structure, `false` otherwise.
   * @throws ClassNotFoundException If the entity class does not exist.
   */
  public function hasValidEntityStructure(object|array $entity, string $entityClass): bool
  {
    if (is_array($entity))
    {
      $entity = (object) $entity;
    }

    if (!class_exists($entityClass))
    {
      throw new ClassNotFoundException(className: $entityClass);
    }

    foreach ($entity as $propertyName => $propertyValue)
    {
      if (!property_exists($entityClass, $propertyName))
      {
        return false;
      }
    }

    return true;
  }

  /**
   * Converts a DateTime object to a string.
   *
   * @param DateTime $property The DateTime object to convert.
   * @param string $propName The name of the property.
   * @param array $options The options to use when converting the DateTime object.
   * @return string Returns the DateTime object as a string.
   */
  public function convertDateTimeToString(DateTime $property, string $propName, array $options): string
  {
    $dateTimeFormat = DATE_ATOM;

    if (isset($options['columnTypes']))
    {
      /** @var ColumnType $columnType */
      $columnType = $options['columnTypes'][$propName];
      $dateTimeFormat = match ($columnType) {
        ColumnType::DATE => 'Y-m-d',
        ColumnType::TIME => 'h:i:s',
        ColumnType::DATETIME => 'Y-m-d h:i:s',
        default => DATE_ATOM
      };
    }

    return $property->format($dateTimeFormat);
  }
}