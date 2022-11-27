<?php

namespace Assegai\Orm\Queries\Sql;

use JetBrains\PhpStorm\ArrayShape;

final class SQLQueryResult
{
  /**
   * @param array $data
   * @param array $errors
   * @param bool $isOK
   */
  public function __construct(
    private readonly array $data,
    private readonly array $errors = [],
    private readonly bool  $isOK = true,
  )
  {
  }

  /**
   * @return bool
   */
  public function isSuccessful(): bool
  {
    return $this->isOK;
  }

  /**
   * @return bool
   * @deprecated 1.0.0 No longer used by internal code and not recommended. Use SQLQueryResult::isSuccessful() instead.
   */
  public function isOK(): bool
  {
    return $this->isOK;
  }

  /**
   * @return bool
   */
  public function isError(): bool
  {
    return $this->isOK === false;
  }

  /**
   * @return array
   */
  public function value(): array
  {
    return $this->isOK ? $this->data : $this->errors;
  }

  /**
   * @return array
   */
  #[ArrayShape(['isOK' => "bool", 'value' => "array", 'errors' => "array"])]
  public function toArray(): array
  {
    return [
      'isOK'    => $this->isSuccessful(),
      'value'   => $this->value(),
      'errors'  => $this->errors
    ];
  }

  /**
   * @return string
   */
  public function toJSON(): string
  {
    return json_encode($this->value());
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    return $this->toJSON();    
  }

  /**
   * @param mixed $needle
   * @return bool
   */
  public function contains(mixed $needle): bool
  {
    return in_array(needle: $needle, haystack: $this->data, strict: true);
  }

  /**
   * @param mixed $needle
   * @return bool
   */
  public function containsError(mixed $needle): bool
  {
    return in_array(needle: $needle, haystack: $this->errors, strict: true);
  }
}