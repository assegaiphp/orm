<?php

namespace Assegai\Orm\Interfaces;

/**
 * A scope that owns a store.
 */
interface IStoreOwner
{
  /**
   * @param string|null $name
   * @return array
   */
  public function getStore(?string $name = null): array;

  /**
   * @param $string
   * @return object|null
   */
  public function getStoreEntry($string): ?object;

  /**
   * @param string $key
   * @param object $value
   * @return int
   */
  public function addStoreEntry(string $key, object $value): int;

  /**
   * @param string $key
   * @param object $value
   * @return int
   */
  public function removeStoreEntry(string $key, object $value): int;

  /**
   * @param string $key
   * @return bool
   */
  public function hasStoreEntry(string $key): bool;
}