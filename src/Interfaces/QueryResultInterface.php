<?php

namespace Assegai\Orm\Interfaces;

/**
 * Interface ResultInterface
 * @package Assegai\Orm\Interfaces
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
   * @return mixed Result data.
   */
  public function getData(): mixed;

  /**
   * Returns the raw result.
   *
   * @return mixed Raw result.
   */
  public function getRaw(): mixed;
}