<?php

namespace Assegai\Orm\Interfaces;

/**
 *
 */
interface IDataCollection
{
  /**
   * @param mixed $values
   * @param ...$args
   * @return int|object|array
   */
  public function create(mixed $values, ...$args): int|object|array;

  /**
   * @param mixed $predicate
   * @param ...$args
   * @return array|null
   */
  public function find(mixed $predicate, ...$args): ?array;

  /**
   * @param mixed $predicate
   * @param mixed $values
   * @param ...$args
   * @return int|object|array
   */
  public function update(mixed $predicate, mixed $values, ...$args): int|object|array;

  /**
   * @param mixed $predicate
   * @param ...$args
   * @return int|null
   */
  public function remove(mixed $predicate, ...$args): ?int;

  /**
   * @param mixed $predicate
   * @param ...$args
   * @return int
   */
  public function count(mixed $predicate, ...$args): int;

  /**
   * @param mixed $predicate
   * @param ...$args
   * @return int|float
   */
  public function average(mixed $predicate, ...$args): int|float;

  /**
   * @param mixed $predicate
   * @param ...$args
   * @return int|float
   */
  public function max(mixed $predicate, ...$args): int|float;

  /**
   * @param mixed $predicate
   * @param ...$args
   * @return int|float
   */
  public function min(mixed $predicate, ...$args): int|float;

  /**
   * @param mixed $predicate
   * @return int|float
   */
  public function sum(mixed $predicate): int|float;
}