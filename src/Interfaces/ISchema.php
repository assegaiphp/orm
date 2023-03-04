<?php

namespace Assegai\Orm\Interfaces;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\SchemaOptions;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Metadata\SchemaMetadata;
use PDO;

/**
 * The `Schema` class provides a database agnostic way of manipulating tables.
 * It works well with all the databases supported by
 * [Assegai](https://assegai.io/docs/supported-databases), and has a unified
 * API across all of these systems.
 */
interface ISchema
{
  /**
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return bool|null
   */
  public static function create(string $entityClass, ?SchemaOptions $options = null): ?bool;

  /**
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return bool|null
   */
  public static function createIfNotExists(string $entityClass, ?SchemaOptions $options = null): ?bool;

  /**
   * @param string $from
   * @param string $to
   * @param SchemaOptions|null $options
   * @return bool|null
   */
  public static function rename(string $from, string $to, ?SchemaOptions $options = null): ?bool;

  /**
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return bool|null
   */
  public static function alter(string $entityClass, ?SchemaOptions $options = null): ?bool;

  /**
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return SchemaMetadata|null
   */
  public static function info(string $entityClass, ?SchemaOptions $options = null): ?SchemaMetadata;

  /**
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return bool|null
   */
  public static function truncate(string $entityClass, ?SchemaOptions $options = null): ?bool;

  /**
   * Drops a database table for the given entity class.
   *
   * @param string $entityClass The class name of the entity representing the table to be dropped.
   * @param SchemaOptions|null $options (Optional) The schema options to use.
   *
   * @return bool|null True if the table was dropped successfully, or null if an error occurred.
   */
  public static function drop(string $entityClass, ?SchemaOptions $options = null): ?bool;

  /**
   * Drops a database table for the given entity class, if it exists.
   *
   * @param string $entityClass The class name of the entity representing the table to be dropped.
   * @param SchemaOptions|null $options (Optional) The schema options to use.
   *
   * @return bool|null True if the table was dropped successfully, false if it doesn't exist, and null if an error occurred.
   */
  public static function dropIfExists(string $entityClass, ?SchemaOptions $options = null): ?bool;

  /**
   * Checks whether a table exists in the database.
   *
   * @param string $tableName The name of the table to check.
   * @param DataSource $dataSource The data source to use for the query.
   *
   * @return bool True if the table exists, false otherwise.
   */
  public static function exists(string $tableName, DataSource $dataSource): bool;

  /**
   * Determines whether a table has the specified columns.
   *
   * @param string $tableName The name of the table to check.
   * @param array $columnNames An array of column names to check for.
   * @param DataSource $dataSource The data source to use for the query.
   *
   * @return bool True if the table has all specified columns, false otherwise.
   */
  public static function hasColumns(string $tableName, array $columnNames, DataSource $dataSource): bool;
}