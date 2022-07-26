<?php

namespace Assegai\Orm\Management;

/**
 *
 */
class FindManyOptions extends FindOneOptions
{
  /** @noinspection PhpMissingParentConstructorInspection */
  public function __construct(
    public readonly ?int $skip = null,
    public readonly ?int $limit = null,
    public readonly array $exclude = ['password'],
  ) { }

  /**
   * @return string
   */
  public function __toString(): string
  {
    return "LIMIT {$this->limit} OFFSET {$this->skip}";
  }
}