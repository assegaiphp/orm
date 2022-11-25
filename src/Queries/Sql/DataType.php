<?php

namespace Assegai\Orm\Queries\Sql;

class DataType
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
        DataType::TINYINT,
        DataType::TINYINT_UNSIGNED,
        DataType::BOOLEAN,
        DataType::SMALLINT,
        DataType::SMALLINT_UNSIGNED,
        DataType::MEDIUMINT,
        DataType::MEDIUMINT_UNSIGNED,
        DataType::INT,
        DataType::INT_UNSIGNED,
        DataType::BIGINT,
        DataType::BIGINT_UNSIGNED,
        DataType::DECIMAL,
        DataType::FLOAT,
        DataType::DOUBLE,
        DataType::BIT,
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
        DataType::BINARY,
        DataType::BLOB,
        DataType::TEXT,
        DataType::CHAR,
        DataType::ENUM,
        DataType::INET6,
        DataType::JSON,
        DataType::MEDIUMBLOB,
        DataType::MEDIUMTEXT,
        DataType::LONGBLOB,
        DataType::LONGTEXT,
        DataType::ROW,
        DataType::TINYBLOB,
        DataType::TINYTEXT,
        DataType::VARBINARY,
        DataType::VARCHAR,
        DataType::SET,
        DataType::UUID,
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
        DataType::DATE,
        DataType::TIME,
        DataType::DATETIME,
        DataType::TIMESTAMP,
        DataType::YEAR,
        DataType::AUTO_INCREMENT,
        DataType::NULL,
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
        DataType::POINT,
        DataType::LINESTRING,
        DataType::POLYGON,
        DataType::MULTIPOINT,
        DataType::MULTILINESTRING,
        DataType::MULTIPOLYGON,
        DataType::GEOMETRYCOLLECTION,
        DataType::GEOMETRY,
      ]
    );
  }
}
