<?php

namespace Assegai\Orm\Management;

use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Exceptions\ORMException;
use ReflectionClass;
use ReflectionException;

/**
 *
 */
final readonly class FindWhereOptions
{
  private ?string $tableName;

  /**
   * @param object|array $conditions
   * @param array $exclude
   * @param string|null $entityClass
   */
  public function __construct(
    public object|array $conditions,
    public array        $exclude = ['password'],
    private ?string     $entityClass = null,
  )
  {
    if ($this->entityClass)
    {
      try
      {
        $reflectionClass = new ReflectionClass($this->entityClass);
        $entityAttributes = $reflectionClass->getAttributes(Entity::class);

        foreach ($entityAttributes as $entityAttribute)
        {
          /** @var Entity $entityMetadata */
          $entityMetadata = (object)$entityAttribute->getArguments();
          $this->tableName = $entityMetadata->table;
        }
      }
      catch (ReflectionException $e)
      {
        die(new ORMException($e->getMessage()));
      }
    }
  }

  /**
   * @param array $options
   * @return FindWhereOptions
   */
  public static function fromArray(array $options): FindWhereOptions
  {
    $conditions = $options['conditions'] ?? $options ?? [];
    $exclude = $options['exclude'] ?? ['password'];

    return new FindWhereOptions(conditions: $conditions, exclude: $exclude);
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    $output = '';

    foreach ($this->conditions as $key => $value)
    {
      $tableName = $this->tableName ?? '';
      if ($tableName)
      {
        $tableName = $tableName . '.';
      }
      $value = match (true) {
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