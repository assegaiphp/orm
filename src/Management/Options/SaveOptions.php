<?php

namespace Assegai\Orm\Management\Options;

/**
 * Defines the save
 */
readonly class SaveOptions
{
  /**
   * Constructs the SaveOptions.
   * 
   * @param mixed $data Additional data to be passed with remove method.
   * This data can be used in subscribers then.
   * 
   * @param null|bool $listeners Indicates if listeners and subscribers are called for this operation.
   * By default, they are enabled, you can disable them by setting { listeners: false } in save/remove options.
   *
   * @param null|bool $transaction By default, transactions are enabled and all queries in persistence operation are
   * wrapped into the transaction.
   * You can disable this behaviour by setting { transaction: false } in the persistence options.
   *
   * @param null|int $chunk Breaks save execution into given number of chunks.
   * For example, if you want to save 100,000 objects, but you have issues with saving them,
   * you can break them into 10 groups of 10,000 objects (by setting { chunk: 10000 }) and save each group separately.
   * This option is needed to perform very big insertions when you have issues with underlying driver parameter number
   * limitation.
   * 
   * @param null|bool $reload Flag to determine whether the entity that is being persisted
   * should be reloaded during the persistence operation.
   *
   * It will work only on databases which does not support RETURNING / OUTPUT statement.
   * Enabled by default.
   */
  public function __construct(
    public mixed $data = null,
    public ?bool $listeners = true,
    public ?bool $transaction = false,
    public ?int  $chunk = null,
    public ?bool $reload = null,
    public ?array $readonlyColumns = null,
  ) { }

  /**
   * @param array $options
   * @return SaveOptions
   */
  public static function fromArray(array $options): SaveOptions
  {
    $data = $options['data'] ?? null;
    $listeners = $options['listeners'] ?? true;
    $transaction = $options['transaction'] ?? false;
    $chunk = $options['chunk'] ?? null;
    $reload = $options['reload'] ?? null;
    $readonlyColumns = $options['readonlyColumns'] ?? null;

    return new SaveOptions(
      data: $data,
      listeners: $listeners,
      transaction: $transaction,
      chunk: $chunk,
      reload: $reload,
      readonlyColumns: $readonlyColumns,
    );
  }
}