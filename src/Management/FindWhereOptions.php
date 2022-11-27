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
      $value = match (true) {
        (bool)preg_match('/[!@#$%^&*()_\-+=\/\\\[\],]+/', $value) => "'$value'",
        default => $value
      };
      $output .= ((is_null($value) || $value === 'NULL') ? "$key IS $value" : "$key=$value") . ' AND ';
    }

    return trim($output, ' AND');
  }
}