<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Interfaces\QueryResultInterface;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class SQLQueryResult. Represents the result of a SQL query.
 * @package Assegai\Orm\Queries\Sql
 *
 * @template T
 */
final readonly class SQLQueryResult implements QueryResultInterface
{
  /**
   * Constructs a new SQLQueryResult instance.
   *
   * @param array $data The data.
   * @param array $errors The errors.
   * @param mixed|null $raw
   */
  public function __construct(
    private array $data,
    private array $errors = [],
    private mixed $raw = null,
    private int $affected = 0
  )
  {
  }

  /**
   * @return bool
   * @deprecated 1.0.0 No longer used by internal code and not recommended. Use SQLQueryResult::isSuccessful() instead.
   */
  public function isSuccessful(): bool
  {
    return $this->isOK();
  }

  /**
   *
   * @return bool
   */
  public function isOK(): bool
  {
    return empty($this->errors);
  }

  /**
   *
   * @return bool
   */
  public function isError(): bool
  {
    return !$this->isOK();
  }

  /**
   * @return array
   */
  public function value(): array
  {
    return $this->isOK() ? $this->data : $this->errors;
  }

  /**
   * @return array
   */
  #[ArrayShape(['isOK' => "bool", 'isError' => "bool", 'value' => "array", 'errors' => "array"])]
  public function toArray(): array
  {
    return [
      'isOK'    => $this->isOK(),
      'isError'    => $this->isError(),
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

  /**
   * @inheritDoc
   */
  public function getErrors(): array
  {
    return $this->errors;
  }

  /**
   * @inheritDoc
   * @return array
   */
  public function getData(): array
  {
    return $this->data;
  }

  /**
   * @inheritDoc
   */
  public function getRaw(): mixed
  {
    return $this->raw;
  }

  /**
   * @inheritDoc
   */
  public function getTotalAffectedRows(): int
  {
    return $this->affected;
  }
}