<?php

namespace Assegai\Orm\Queries\Sql;

class ColumnType
{
  /* Numeric Data Types */
  const TINYINT = 'TINYINT';
  const TINYINT_UNSIGNED = 'TINYINT UNSIGNED';
  const BOOLEAN = 'BOOLEAN';
  const SMALLINT = 'SMALLINT';
  const SMALLINT_UNSIGNED = 'SMALLINT UNSIGNED';
  const MEDIUMINT = 'MEDIUMINT';
  const MEDIUMINT_UNSIGNED = 'MEDIUMINT UNSIGNED';
  const INT = 'INT';
  const INT_UNSIGNED = 'INT UNSIGNED';
  const BIGINT = 'BIGINT';
  const BIGINT_UNSIGNED = 'BIGINT UNSIGNED';
  const DECIMAL = 'DECIMAL';
  const FLOAT = 'FLOAT';
  const DOUBLE = 'DOUBLE';
  const BIT = 'BIT';

  /* String Data Types */
  const BINARY = 'BINARY';
  const BLOB = 'BLOB';
  const TEXT = 'TEXT';
  const CHAR = 'CHAR';
  const ENUM = 'ENUM';
  const INET6 = 'INET6';
  const JSON = 'JSON';
  const MEDIUMBLOB = 'MEDIUMBLOB';
  const MEDIUMTEXT = 'MEDIUMTEXT';
  const LONGBLOB = 'LONGBLOB';
  const LONGTEXT = 'LONGTEXT';
  const ROW = 'ROW';
  const TINYBLOB = 'TINYBLOB';
  const TINYTEXT = 'TINYTEXT';
  const VARBINARY = 'VARBINARY';
  const VARCHAR = 'VARCHAR';
  const SET = 'SET';
  const UUID = 'UUID';

  /* Date and Time Data Types */
  const DATE = 'DATE';
  const TIME = 'TIME';
  const DATETIME = 'DATETIME';
  const TIMESTAMP = 'TIMESTAMP';
  const YEAR = 'YEAR';
  const AUTO_INCREMENT = 'AUTO_INCREMENT';
  const NULL = 'NULL';

  /* Geometry and Spatial Data Types */
  const POINT = 'POINT';
  const LINESTRING = 'LINESTRING';
  const POLYGON = 'POLYGON';
  const MULTIPOINT = 'MULTIPOINT';
  const MULTILINESTRING = 'MULTILINESTRING';
  const MULTIPOLYGON = 'MULTIPOLYGON';
  const GEOMETRYCOLLECTION = 'GEOMETRYCOLLECTION';
  const GEOMETRY = 'GEOMETRY';

  /**
   * @param string $type
   * @return bool
   */
  public static function isNumeric(string $type): bool
  {
    return in_array(
      $type,
      [
        ColumnType::TINYINT,
        ColumnType::TINYINT_UNSIGNED,
        ColumnType::BOOLEAN,
        ColumnType::SMALLINT,
        ColumnType::SMALLINT_UNSIGNED,
        ColumnType::MEDIUMINT,
        ColumnType::MEDIUMINT_UNSIGNED,
        ColumnType::INT,
        ColumnType::INT_UNSIGNED,
        ColumnType::BIGINT,
        ColumnType::BIGINT_UNSIGNED,
        ColumnType::DECIMAL,
        ColumnType::FLOAT,
        ColumnType::DOUBLE,
        ColumnType::BIT,
      ]
    );
  }

  /**
   * @param string $type
   * @return bool
   */
  public static function isString(string $type): bool
  {
    return in_array(
      $type,
      [
        ColumnType::BINARY,
        ColumnType::BLOB,
        ColumnType::TEXT,
        ColumnType::CHAR,
        ColumnType::ENUM,
        ColumnType::INET6,
        ColumnType::JSON,
        ColumnType::MEDIUMBLOB,
        ColumnType::MEDIUMTEXT,
        ColumnType::LONGBLOB,
        ColumnType::LONGTEXT,
        ColumnType::ROW,
        ColumnType::TINYBLOB,
        ColumnType::TINYTEXT,
        ColumnType::VARBINARY,
        ColumnType::VARCHAR,
        ColumnType::SET,
        ColumnType::UUID,
      ]
    );
  }

  /**
   * @param string $type
   * @return bool
   */
  public static function isDateTime(string $type): bool
  {
    return in_array(
      $type,
      [
        ColumnType::DATE,
        ColumnType::TIME,
        ColumnType::DATETIME,
        ColumnType::TIMESTAMP,
        ColumnType::YEAR,
        ColumnType::AUTO_INCREMENT,
        ColumnType::NULL,
      ]
    );
  }

  /**
   * @param string $type
   * @return bool
   */
  public static function isGeoSpatial(string $type): bool
  {
    return in_array(
      $type,
      [
        ColumnType::POINT,
        ColumnType::LINESTRING,
        ColumnType::POLYGON,
        ColumnType::MULTIPOINT,
        ColumnType::MULTILINESTRING,
        ColumnType::MULTIPOLYGON,
        ColumnType::GEOMETRYCOLLECTION,
        ColumnType::GEOMETRY,
      ]
    );
  }
}
