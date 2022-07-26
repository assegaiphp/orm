<?php

namespace Assegai\Orm\Enumerations;

final class OrphanedRowActionType
{
  public function __construct(
    private readonly ?string $value = null
  ) { }

  public function __toString(): string
  {
    return $this->value;
  }

  /**
   * @return OrphanedRowActionType
   */
  public static function nullify(): OrphanedRowActionType
  {
    return new OrphanedRowActionType(value: 'nullify');
  }

  /**
   * @return OrphanedRowActionType
   */
  public static function delete(): OrphanedRowActionType
  {
    return new OrphanedRowActionType(value: 'delete');
  }
}
