<?php

namespace Assegai\Orm\Queries\Sql;

enum ColumnType: string
{
  /* Numeric Data Types */
  case TINYINT = 'TINYINT';
  case TINYINT_UNSIGNED = 'TINYINT UNSIGNED';
  case BOOLEAN = 'BOOLEAN';
  case SMALLINT = 'SMALLINT';
  case SMALLINT_UNSIGNED = 'SMALLINT UNSIGNED';
  case MEDIUMINT = 'MEDIUMINT';
  case MEDIUMINT_UNSIGNED = 'MEDIUMINT UNSIGNED';
  case INT = 'INT';
  case INT_UNSIGNED = 'INT UNSIGNED';
  case BIGINT = 'BIGINT';
  case BIGINT_UNSIGNED = 'BIGINT UNSIGNED';
  case DECIMAL = 'DECIMAL';
  case FLOAT = 'FLOAT';
  case DOUBLE = 'DOUBLE';
  case BIT = 'BIT';

  /* String Data Types */
  case BINARY = 'BINARY';
  case BLOB = 'BLOB';
  case TEXT = 'TEXT';
  case CHAR = 'CHAR';
  case ENUM = 'ENUM';
  case INET6 = 'INET6';
  case JSON = 'JSON';
  case MEDIUMBLOB = 'MEDIUMBLOB';
  case MEDIUMTEXT = 'MEDIUMTEXT';
  case LONGBLOB = 'LONGBLOB';
  case LONGTEXT = 'LONGTEXT';
  case ROW = 'ROW';
  case TINYBLOB = 'TINYBLOB';
  case TINYTEXT = 'TINYTEXT';
  case VARBINARY = 'VARBINARY';
  case VARCHAR = 'VARCHAR';
  case SET = 'SET';
  case UUID = 'UUID';

  /* Date and Time Data Types */
  case DATE = 'DATE';
  case TIME = 'TIME';
  case DATETIME = 'DATETIME';
  case TIMESTAMP = 'TIMESTAMP';
  case YEAR = 'YEAR';
  case AUTO_INCREMENT = 'AUTO_INCREMENT';
  case NULL = 'NULL';

  /* Geometry and Spatial Data Types */
  case POINT = 'POINT';
  case LINESTRING = 'LINESTRING';
  case POLYGON = 'POLYGON';
  case MULTIPOINT = 'MULTIPOINT';
  case MULTILINESTRING = 'MULTILINESTRING';
  case MULTIPOLYGON = 'MULTIPOLYGON';
  case GEOMETRYCOLLECTION = 'GEOMETRYCOLLECTION';
  case GEOMETRY = 'GEOMETRY';

  /**
   * @param string $type
   * @return bool
   */
  public function isNumeric(): bool
  {
    return match($this) {
      self::TINYINT,
      self::TINYINT_UNSIGNED,
      self::BOOLEAN,
      self::SMALLINT,
      self::SMALLINT_UNSIGNED,
      self::MEDIUMINT,
      self::MEDIUMINT_UNSIGNED,
      self::INT,
      self::INT_UNSIGNED,
      self::BIGINT,
      self::BIGINT_UNSIGNED,
      self::DECIMAL,
      self::FLOAT,
      self::DOUBLE,
      self::BIT => true,
      default => false
    };
  }

  /**
   * @param string $type
   * @return bool
   */
  public function isString(): bool
  {
    return match ($this) {
      self::BINARY,
      self::BLOB,
      self::TEXT,
      self::CHAR,
      self::ENUM,
      self::INET6,
      self::JSON,
      self::MEDIUMBLOB,
      self::MEDIUMTEXT,
      self::LONGBLOB,
      self::LONGTEXT,
      self::ROW,
      self::TINYBLOB,
      self::TINYTEXT,
      self::VARBINARY,
      self::VARCHAR,
      self::SET,
      self::UUID => true,
      default => false
    };
  }

  /**
   * @param string $type
   * @return bool
   */
  public function isDateTime(): bool
  {
    return match($this) {
      self::DATE,
      self::TIME,
      self::DATETIME,
      self::TIMESTAMP,
      self::YEAR,
      self::AUTO_INCREMENT,
      self::NULL => true,
      default => false
    };
  }

  /**
   * @param string $type
   * @return bool
   */
  public function isGeoSpatial(string $type): bool
  {
    return match($this) {
      self::POINT,
      self::LINESTRING,
      self::POLYGON,
      self::MULTIPOINT,
      self::MULTILINESTRING,
      self::MULTIPOLYGON,
      self::GEOMETRYCOLLECTION,
      self::GEOMETRY => true,
      default => false
    };
  }

  public function defaultValue(): mixed
  {
    return match (true) {
      $this->isNumeric() => match ($this) {
        self::TINYINT => 4,
        self::SMALLINT => 6,
        self::MEDIUMINT => 9,
        self::INT => 11,
        self::BIGINT => 20,
        self::DECIMAL => '10,0',
        self::BIT => 8,
        default => null
      },
      $this->isString() => match ($this) {
        self::CHAR => 191,
        self::VARCHAR => 255,
        self::TEXT => 65535,
        default => null,
      },
      default => null
    };
  }
}
