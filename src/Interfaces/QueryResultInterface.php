<?php

namespace Assegai\Orm\Interfaces;

/**
 * Interface ResultInterface
 * @package Assegai\Orm\Interfaces
 *
 * @template T
 * @template-implements QueryResultInterface<T>
 */
interface QueryResultInterface
{
  /**
   * Returns true if the result is ok.
   *
   * @return bool True if the result is ok.
   */
  public function isOk(): bool;

  /**
   * Returns true if the result is an error.
   *
   * @return bool True if the result is an error.
   */
  public function isError(): bool;

  /**
   * Returns a list of errors.
   *
   * @return array List of errors.
   */
  public function getErrors(): array;

  /**
   * Returns the result data.
   *
   * @return mixed<T> Result data.
   */
  public function getData(): mixed;

  /**
   * Returns the raw result.
   *
   * @return mixed Raw result.
   */
  public function getRaw(): mixed;

  /**
   * Returns the number of affected rows.
   *
   * @return int Number of affected rows.
   */
  public function getTotalAffectedRows(): int;
}