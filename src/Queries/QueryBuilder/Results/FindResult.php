<?php

namespace Assegai\Orm\Queries\QueryBuilder\Results;

use Assegai\Orm\Interfaces\QueryResultInterface;

/**
 * Class FindResult represents a result of a find query.
 * @package Assegai\Orm\Queries\QueryBuilder\Results
 */
readonly class FindResult implements QueryResultInterface
{
  /**
   * @param mixed $raw Raw SQL result returned by executed query.
   * @param mixed $data The result data.
   * @param array $errors List of errors.
   */
  public function __construct(
    protected mixed $raw,
    protected mixed $data,
    protected array $errors = []
  )
  {
  }

  /**
   * @inheritDoc
   */
  public function isOk(): bool
  {
    return empty($this->errors);
  }

  /**
   * @inheritDoc
   */
  public function isError(): bool
  {
    return !$this->isOk();
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
   */
  public function getData(): mixed
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
}