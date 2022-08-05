<?php

namespace Assegai\Orm\Interfaces;

interface IDataCollectionOwner
{
  public function create(string $name, mixed $options, ...$args): int|object|null;

  public function rename(string $name): void;

  public function remove(mixed $predicate): void;

  public function exists(string $name): bool;

  public function findCollection(mixed $predicate): ?IDataCollection;

  public function getDriver(): object;


}