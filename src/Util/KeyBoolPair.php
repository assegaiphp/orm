<?php

namespace Assegai\Orm\Util;

use JetBrains\PhpStorm\ArrayShape;

/**
 * A convenience class to represent key-value pairs with values of the bool type.
 */
final class KeyBoolPair
{
  /**
   * @param string $key
   * @param bool $value
   */
  public function __construct(
    public readonly string $key,
    public readonly bool $value
  ) { }

  /**
   * @return array
   */
  #[ArrayShape(['key' => "string", 'value' => "bool"])]
  public function toArray(): array
  {
    return ['key' => $this->key, 'value' => $this->value ];
  }

  /**
   * @return string
   */
  public function toJSON(): string
  {
    return json_encode($this->toArray());
  }

  /**
   * @return array
   */
  #[ArrayShape(['key' => "string", 'value' => "bool"])]
  public function __serialize(): array
  {
    return $this->toArray(); 
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    return $this->toJSON();
  }
}