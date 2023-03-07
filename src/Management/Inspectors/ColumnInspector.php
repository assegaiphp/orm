<?php

namespace Assegai\Orm\Management\Inspectors;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Queries\Sql\ColumnType;
use ReflectionException;
use ReflectionProperty;

/**
 * ColumnInspector is a utility class for inspecting columns in entities.
 * It provides methods for retrieving metadata associated with a specific column.
 * This class is part of the Assegai\Orm\Management namespace.
 */
class ColumnInspector
{
  /**
   * The singleton instance of this class.
   * @var ColumnInspector|null
   */
  protected static ?ColumnInspector $instance = null;

  /**
   * Private constructor to prevent creating multiple instances of this class.
   */
  private function __construct()
  {}

  /**
   * Get the singleton instance of this class.
   *
   * @return ColumnInspector The singleton instance of this class.
   */
  public static function getInstance(): static
  {
    if (!self::$instance)
    {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * Retrieve the metadata of a property using its Column attribute.
   *
   * @param object $entity The entity containing the property.
   * @param string $propertyName The name of the property.
   * @return Column|null The Column attribute of the property, or null if it doesn't exist.
   * @throws ORMException if the property doesn't have a Column attribute.
   * @throws ReflectionException if the class or property does not exist.
   */
  public function getMetaData(object $entity, string $propertyName): ?Column
  {
    return $this->getMetaDataFromReflection(new ReflectionProperty($entity, $propertyName));
  }

  /**
   * Retrieves metadata of a property from its reflection, including its associated Column attribute.
   * @param ReflectionProperty $propertyReflection The reflection of the property to retrieve metadata for.
   * @throws ORMException If the property does not have a Column attribute.
   * @return Column|null The Column attribute associated with the property.
   */
  public function getMetaDataFromReflection(ReflectionProperty $propertyReflection): ?Column
  {
    if (!$this->propertyHasColumnAttribute($propertyReflection))
    {
      throw new ORMException("Invalid property. $propertyReflection->name does not have a Column attribute");
    }

    $attributes = array_filter($propertyReflection->getAttributes(), fn($attribute) => is_a($attribute->getName(), Column::class, true) || is_subclass_of($attribute->getName(), Column::class, true));
    return $attributes[0]->newInstance();
  }

  /**
   * Check whether a property has a Column attribute.
   *
   * @param ReflectionProperty $property The property to check.
   * @return bool True if the property has a Column attribute, false otherwise.
   */
  public function propertyHasColumnAttribute(ReflectionProperty $property): bool
  {
    $attributes = $property->getAttributes();

    foreach ($attributes as $attribute)
    {
      if (
        is_a($attribute->getName(), Column::class, true) ||
        is_subclass_of($attribute->getName(), Column::class, true)
      )
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Returns the field name of the given column.
   *
   * @param Column $column The Column attribute to retrieve the field name from.
   * @return string The field name of the column.
   */
  public function getField(Column $column): string
  {
    return $column->name;
  }

  /**
   * Returns the field name of the given column.
   *
   * @param Column $column The Column attribute to retrieve the field name from.
   * @return string The field name of the column.
   */
  public function getType(Column $column): string
  {
    $type = match ($column->type) {
      ColumnType::VARCHAR => ColumnType::VARCHAR->value . "(" . $column->lengthOrValues . ")",
      ColumnType::BOOLEAN => ColumnType::TINYINT->value . "(1)",
      ColumnType::ENUM => ColumnType::ENUM->value . '(' . (is_string($column->enum) ? $column->enum : implode(',', array_map(fn($color) => $color->value, $column->enum::cases()))) . ')',
      default => $column->type
    };
    return strtolower($type);
  }

  /**
   * Returns whether the given column can be null.
   *
   * @param Column $column The Column attribute to retrieve the nullability from.
   * @return string 'YES' if the column can be null, 'NO' otherwise.
   */
  public function getNull(Column $column): string
  {
    return $column->nullable ? 'YES' : 'NO';
  }

  /**
   * Returns the key type of the given column.
   *
   * @param Column $column The Column attribute to retrieve the key type from.
   * @return string The key type of the column.
   */
  public function getKey(Column $column): string
  {
    return match (true) {
      $column->isPrimaryKey => 'PRI',
      $column->isUnique => 'UNI',
      default => ''
    };
  }

  /**
   * Returns the default value of the given column.
   *
   * @param Column $column The Column attribute to retrieve the default value from.
   * @return string The default value of the column.
   */
  public function getDefault(Column $column): string
  {
    return $column->default;
  }

  /**
   * Returns any extra information about the column, such as whether it is auto-incrementing.
   *
   * @param Column $column The Column attribute to retrieve the key type from.
   * @return string The extra information about the column.
   */
  public function getExtra(Column $column): string
  {
    $extra = '';

    if ($column->autoIncrement)
    {
      $extra .= 'auto_increment ';
    }

    if ($this->getDefault($column) === 'CURRENT_TIMESTAMP')
    {
      $extra .= 'DEFAULT_GENERATED ';

      if ($column->onUpdate)
      {
        $extra .= "on update $column->onUpdate";
      }
    }

    return trim($extra);
  }
}