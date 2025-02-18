<?php

namespace Assegai\Orm\Management\Options;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Exceptions\ORMException;
use ReflectionClass;
use ReflectionException;
use ReflectionUnionType;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class FindWhereOptions. Defines the options for the FindWhere method.
 *
 * @package Assegai\Orm\Management\Options
 */
final readonly class FindWhereOptions
{
  /**
   * @var string|null
   */
  private ?string $tableName;

  /**
   * @param object|array<string, mixed> $conditions The conditions to search for.
   * @param string[] $exclude The columns to exclude.
   * @param class-string|null $entityClass The entity class.
   * @param bool $withRealTotal The flag to include the real count.
   * @throws ORMException
   */
  public function __construct(
    public object|array $conditions,
    public array        $exclude = ['password'],
    private ?string     $entityClass = null,
    public bool         $withRealTotal = false
  )
  {
    if ($this->entityClass) {
      try {
        $reflectionClass = new ReflectionClass($this->entityClass);
        $entityAttributes = $reflectionClass->getAttributes(Entity::class);

        foreach ($entityAttributes as $entityAttribute) {
          /** @var Entity $entityMetadata */
          $entityMetadata = (object)$entityAttribute->getArguments();
          $this->tableName = $entityMetadata->table;
        }
      } catch (ReflectionException $e) {
        throw new ORMException($e->getMessage());
      }
    }
  }

  /**
   * Creates a new FindWhereOptions instance from an array.
   *
   * @param array{conditions: ?array<string, mixed>, exclude: ?string[], entity_class: ?class-string, with_real_total: ?bool} $options The options.
   * @return FindWhereOptions The FindWhereOptions instance.
   * @throws ORMException
   */
  public static function fromArray(array $options): FindWhereOptions
  {
    $conditions = $options['conditions'] ?? $options;
    $exclude = $options['exclude'] ?? ['password'];
    $entityClassName = $options['entity_class'] ?? null;
    $withRealTotal = $options['with_real_total'] ?? false;

    return new FindWhereOptions(
      conditions: $conditions,
      exclude: $exclude,
      entityClass:  $entityClassName,
      withRealTotal: $withRealTotal
    );
  }

  /**
   * @return string
   * @throws ReflectionException
   */
  public function __toString(): string
  {
    $output = '';
    /** @var array{name: string, type: string} $entityClassProperties */
    $entityClassColumns = [];
    $out = new ConsoleOutput(decorated: true);

    if ($this->entityClass) {
      $entityClassReflection = new ReflectionClass($this->entityClass);
      foreach ($entityClassReflection->getProperties() as $property) {
        $propertyType = $property->getType();
        $type = match(true) {
          $propertyType instanceof ReflectionUnionType => strval($propertyType),
          default => $property->getType()?->getName() ?? ''
        };
        $column = ['name' => $property->getName(), 'type' => $type];
        if (! isset($this->conditions[$column['name']])) {
          $attributes = $property->getAttributes();

          foreach ($attributes as $attribute) {
            $attributeInstance = $attribute->newInstance();
            if ($attributeInstance instanceof Column) {
              $column['name'] = $attribute->getArguments()['name'] ?? $column['name'];
            }
          }
        }

        $entityClassColumns[$column['name']] = $column;
      }
    }

    foreach ($this->conditions as $key => $value) {
      $tableName = $this->tableName ?? '';

      if ($tableName) {
        $tableName = $tableName . '.';
      }

      $value = match (true) {
        isset($entityClassColumns[$key]) => match ($entityClassColumns[$key]['type']) {
          'string'  => "'$value'",
          'int'     => (int)$value,
          'float'   => (float)$value,
          'bool'    => (bool)$value,
          default   => $value
        },
        (bool)preg_match('/[!@#$%^&*()_\-+=\/\\\[\],]+/', $value) => "'$value'",
        default => $value
      };

      $output .= $tableName .
        ((is_null($value) || $value === 'NULL')
          ? "$key IS $value"
          : "$key=$value") . ' AND ';
    }

    return trim($output, ' AND');
  }
}