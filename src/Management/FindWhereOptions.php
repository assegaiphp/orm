<?php

namespace Assegai\Orm\Management;

/**
 *
 */
final class FindWhereOptions
{
  /**
   * @param object|array $conditions
   * @param array $exclude
   */
  public function __construct(
    public readonly object|array $conditions,
    public readonly array $exclude = ['password'],
  )
  {
  }

  /**
   * @param array $options
   * @return FindWhereOptions
   */
  public static function fromArray(array $options): FindWhereOptions
  {
    $conditions = $options['conditions'] ?? [];
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
      $output .= "$key=$value";
    }

    return $output;
  }
}