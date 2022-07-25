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
  public static function renameTable(string $from, string $to, ?SchemaOptions $options = null): ?bool;

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
  public static function dropTable(string $entityClass, ?SchemaOptions $options = null): ?bool;

  /**
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return bool|null
   */
  public static function dropTableIfExists(string $entityClass, ?SchemaOptions $options = null): ?bool;

  /**
   * @param PDO|DataSource $dataSource
   * @param string $databaseName
   * @return bool
   */
  public static function dbExists(PDO|DataSource $dataSource, string $databaseName): bool;

  /**
   * @param PDO|DataSource $dataSource
   * @param string $databaseName
   * @param string $tableName
   * @param SQLDialect $dialect
   * @return bool
   */
  public static function dbTableExists(PDO|DataSource $dataSource, string $databaseName, string $tableName, SQLDialect $dialect = SQLDialect::MYSQL): bool;
}