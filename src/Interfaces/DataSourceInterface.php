<?php

namespace Assegai\Orm\Interfaces;

use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\Exceptions\DataSourceConnectionException;
use Assegai\Orm\Exceptions\DataSourceException;

/**
 * Interface DataSourceInterface
 *
 * This interface defines the methods that should be implemented by all DataSource classes.
 *
 * @package Assegai\Orm\Interfaces
 */
interface DataSourceInterface
{
  /**
   * Connect to the data source.
   *
   * @param DataSourceOptions|array|null $options The options to use for the connection.
   * @return void
   * @throws DataSourceException
   * @throws DataSourceConnectionException
   */
  public function connect(DataSourceOptions|array|null $options): void;

  /**
   * Disconnect from the data source.
   *
   * @return void
   * @throws DataSourceConnectionException
   */
  public function disconnect(): void;

  /**
   * Check if the data source is connected.
   *
   * @return bool True if the data source is connected, false otherwise.
   */
  public function isConnected(): bool;

  /**
   * Get the client for the data source.
   *
   * @return mixed The client for the data source.
   */
  public function getClient(): mixed;

  /**
   * Get the name of the data source. The name can be any of the following:
   * - The name of a database.
   * - The name of a file.
   * - The name of a web service.
   * - The name of a cache.
   * - The name of a queue.
   * - The name of a search engine.
   *
   * @return string The name of the data source.
   */
  public function getName(): string;
}