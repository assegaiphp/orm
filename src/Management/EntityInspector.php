<?php

namespace Assegai\Orm\Management;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\ORMException;
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
   * @param array $exclude
   * @return array Returns a list of properties that are marked with the `Column` attribute.
   */
  public function getColumns(object $entity, array $exclude = []): array
  {
    $columns = [];
    $reflectionClass = new ReflectionClass($entity);
    $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

    foreach ($properties as $property)
    {
      if (in_array($property->getName(), $exclude))
      {
        continue;
      }

      $attributes = $property->getAttributes();
      foreach ($attributes as $attribute)
      {
        $attributeInstance = $attribute->newInstance();
        if ($attributeInstance instanceof Column)
        {
          if ($attributeInstance->alias)
          {
            $columns[$attributeInstance->alias] = $attributeInstance->name;
          }
          else if($attributeInstance->name)
          {
            $columns[$property->getName()] = $attributeInstance->name;
          }
          else
          {
            $columns[] = $property->getName();
          }
        }
      }
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
}