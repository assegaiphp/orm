<?php

namespace Assegai\Orm\Interfaces;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\SchemaOptions;
use Assegai\Orm\Enumerations\SQLDialect;
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
   * @return string|null
   */
  public static function info(string $entityClass, ?SchemaOptions $options = null): ?string;

  /**
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return bool|null
   */
  public static function truncate(string $entityClass, ?SchemaOptions $options = null): ?bool;

  /**
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return bool|null
   */
  public static function drop(string $entityClass, ?SchemaOptions $options = null): ?bool;

  /**
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return bool|null
   */
  public static function dropIfExists(string $entityClass, ?SchemaOptions $options = null): ?bool;

  /**
   * @param string $tableName
   * @param DataSource $dataSource
   * @return bool
   */
  public static function exists(string $tableName, DataSource $dataSource): bool;

  /**
   * Checks if the
   * @param string $tableName
   * @param string[] $columnNames
   * @return bool
   */
  public static function hasColumns(string $tableName, array $columnNames): bool;
}