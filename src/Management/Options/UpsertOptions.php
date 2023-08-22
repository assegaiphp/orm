<?php

namespace Assegai\Orm\Management\Options;

use Assegai\Orm\Enumerations\UpsertType;

/**
 * Options for the upsert method.
 */
readonly class UpsertOptions
{
  /**
   * Constructs a new instance of UpsertOptions.
   *
   * @param string[] $conflictPaths The paths to check for conflicts.
   * @param bool $skipUpdateIfNoValuesChanged Whether to skip the update if no values changed.
   * @param UpsertType $upsertType The type of upsert to perform.
   */
  public function __construct(
    public array $conflictPaths,
    public bool $skipUpdateIfNoValuesChanged = true,
    public UpsertType $upsertType = UpsertType::UPSERT,
  )
  {}

  /**
   * Creates a new instance of UpsertOptions from an array.
   *
   * @param array $options The options to create the instance from.
   * @return static The new instance.
   */
  public static function fromArray(array $options): static
  {
    return new UpsertOptions(
      conflictPaths: $options['conflictPaths'] ?? [],
      skipUpdateIfNoValuesChanged: $options['skipUpdateIfNoValuesChanged'] ?? true,
      upsertType: $options['upsertType'] ?? UpsertType::UPSERT,
    );
  }
}