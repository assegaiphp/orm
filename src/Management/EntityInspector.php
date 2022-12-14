<?php

namespace Assegai\Orm\Management;

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
use DateTime;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Provides for entity object introspection.
 */
final class EntityInspector
{
  /**
   * @var EntityInspector|null
   */
  private static ?EntityInspector $instance = null;

  /**
   * Constructs a new EntityInspector
   */
  private final function __construct()
  {
  }

  /**
   * @return EntityInspector
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
   * @param string $entityClass
   * @return void
   * @throws ClassNotFoundException
   * @throws ORMException
   */
  public static function validateEntityName(string $entityClass): void
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
   * Returns a list of class property names that are marked with the `Column` attribute.
   * @param object $entity
   * @param string[] $exclude
   * @param string[] $relations
   * @param RelationPropertyMetadata $relationProperties
   * @return array Returns a list of properties that are marked with the `Column` attribute.
   */
  public function getColumns(
    object $entity,
    array $exclude = [],
    array $relations = [],
    array &$relationProperties = []
  ): array
  {
    $columns = [];
    $reflectionClass = new ReflectionClass($entity);
    $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

    try
    {
      $tableName = $this->getTableName($entity);
    }
    catch (ClassNotFoundException|ORMException $e)
    {
      die($e);
    }

    foreach ($properties as $property)
    {
      if (in_array($property->getName(), $exclude))
      {
        continue;
      }

      $propertyName = $property->getName();
      $attributes = $property->getAttributes();
      foreach ($attributes as $attribute)
      {
        $attributeInstance = $attribute->newInstance();
        if ($attributeInstance instanceof Column)
        {
          if ($attributeInstance->alias)
          {
            $columns[$attributeInstance->alias] = "$tableName." . $attributeInstance->name;
          }
          else if($attributeInstance->name)
          {
            $columns[$propertyName] = "$tableName." . $attributeInstance->name;
          }
          else
          {
            $columns[] = "$tableName." . $propertyName;
          }
        }

        if ($relations)
        {
          if ($attributeInstance instanceof JoinColumn)
          {
            if ($attributeInstance->name)
            {
              $columns[$propertyName] = "$tableName." . $attributeInstance->name;
            }
            else
            {
              $attributeInstance->effectiveColumnName = $this->getColumnName([$propertyName, 'Id']);
              $columns[] = "$tableName." . $attributeInstance->effectiveColumnName;
            }

            if (!$relationProperties[$propertyName])
            {
              $relationProperties[$propertyName] = new RelationPropertyMetadata(reflectionProperty: $property);
            }

            $relationProperties[$propertyName]->joinColumn = $attributeInstance;
          }
          else if ($attributeInstance instanceof JoinTable)
          {
            if (!$relationProperties[$propertyName])
            {
              $relationProperties[$propertyName] = new RelationPropertyMetadata(reflectionProperty: $property);
            }

            $relationProperties[$propertyName]->joinTable = $attributeInstance;
          }
          else if ($attributeInstance instanceof OneToOne)
          {
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
          }
          else if ($attributeInstance instanceof OneToMany)
          {
            if (!$relationProperties[$propertyName])
            {
              $relationProperties[$propertyName] = new RelationPropertyMetadata(reflectionProperty: $property);
            }

            $relationProperties[$propertyName]->relationAttribute = $attributeInstance;
            $relationProperties[$propertyName]->relationAttributeReflection = $attribute;
            $relationProperties[$propertyName]->inflate();
          }
          else if ($attributeInstance instanceof ManyToOne)
          {
            if (!$relationProperties[$propertyName])
            {
              $relationProperties[$propertyName] = new RelationPropertyMetadata(reflectionProperty: $property);
            }

            $relationProperties[$propertyName]->relationAttribute = $attributeInstance;
            $relationProperties[$propertyName]->relationAttributeReflection = $attribute;
            $relationProperties[$propertyName]->inflate();
          }
          else if ($attributeInstance instanceof ManyToMany)
          {
            if (!$relationProperties[$propertyName])
            {
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
   * @param object $entity
   * @param array $exclude
   * @return array
   */
  private function getRelationColumns(object $entity, array $exclude = []): array
  {

    $columns = [];
    $reflectionClass = new ReflectionClass($entity);
    $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

    try
    {
      $tableName = $this->getTableName($entity);
      foreach ($properties as $property)
      {
        $propertyName = $property->getName();
        $columnAttributes = $property->getAttributes(Column::class);

        if (!$columnAttributes || in_array($propertyName, $exclude))
        {
          continue;
        }

        foreach ($columnAttributes as $columnAttribute)
        {
          $attributeInstance = $columnAttribute->newInstance();
          if ($attributeInstance instanceof Column)
          {
            if ($attributeInstance->alias)
            {
              $columns["{$tableName}_" . $attributeInstance->alias] = "$tableName." . $attributeInstance->name;
            }
            else if($attributeInstance->name)
            {
              $columns[$propertyName] = "$tableName." . $attributeInstance->name;
            }
            else
            {
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
   * @param object $entity
   * @param array $exclude
   * @param array $options
   * @return array
   * @throws ClassNotFoundException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function getValues(object $entity, array $exclude = [], array $options = ['filter' => true]): array
  {
    $filterValues = $options['filter'] ?? true;
    $values = [];
    $class = get_class($entity);
    self::validateEntityName($class);
    $columns = $this->getColumns(entity: $entity, exclude: $exclude);

    foreach ($columns as $index => $column)
    {
      $propName = is_numeric($index) ? $column : $index;
      $property = $entity->$propName;

      if (empty($property))
      {
        $columnAttribute = new ReflectionProperty(get_class($entity), $propName);
        $attributes = $columnAttribute->getAttributes();

        foreach ($attributes as $attribute)
        {
          $attrInstance = $attribute->newInstance();
          if (isset($attrInstance->defaultValue) && !empty($attrInstance->defaultValue))
          {
            $property = $attrInstance->defaultValue;
          }
        }
      }

      // TODO: Perform type conversion
      if (is_object($property))
      {
        if (property_exists($property, 'value'))
        {
          $property = $property->value;
        }

        $property = match(true) {
          $property instanceof DateTime => $property->format(DATE_ATOM),
          default => $property
        };
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
   * @param object $entity
   * @return string
   * @throws ClassNotFoundException
   * @throws ORMException
   */
  public function getTableName(object $entity): string
  {
    $tableName = '';

    self::validateEntityName(get_class($entity));
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
   * @param string $className
   * @return string
   */
  private function getTableNameFromClassName(string $className): string
  {
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
}