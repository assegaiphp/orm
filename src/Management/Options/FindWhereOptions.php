<?php

namespace Assegai\Orm\Management\Options;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Exceptions\ORMException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 *
 */
final readonly class FindWhereOptions
{
  /**
   * @var string|null
   */
  private ?string $tableName;

  /**
   * @param object|array $conditions
   * @param array $exclude
   * @param string|null $entityClass
   * @throws ORMException
   */
  public function __construct(
    public object|array $conditions,
    public array        $exclude = ['password'],
    private ?string     $entityClass = null,
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
   *
   * @param array $options
   * @return FindWhereOptions
   * @throws ORMException
   */
  public static function fromArray(array $options): FindWhereOptions
  {
    $conditions = $options['conditions'] ?? $options;
    $exclude = $options['exclude'] ?? ['password'];

    return new FindWhereOptions(conditions: $conditions, exclude: $exclude);
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
        $column = ['name' => $property->getName(), 'type' => $property->getType()?->getName() ?? ''];
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