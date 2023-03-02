<?php

namespace Assegai\Orm\Enumerations;

/**
 * An enumeration of various Schema Engine types.
 */
enum SchemaEngineType: string
{
  /**
   * Storage engine for storing historical records that are rarely accessed.
   */
  case ARCHIVE = 'ARCHIVE';

  /**
   * Storage engine that accepts data but does not store it.
   */
  case BLACKHOLE = 'BLACKHOLE';

  /**
   * Collection of identical MyISAM tables.
   */
  case MRG_MYISAM = 'MRG_MYISAM';

  /**
   * A storage engine that allows a table to be a proxy to a table located on a different server.
   */
  case FEDERATED = 'FEDERATED';

  /**
   * The default storage engine in MySQL 3.23 through 5.5.
   */
  case MY_ISAM = 'MyISAM';

  /**
   * A storage engine that provides access to performance_schema tables.
   */
  case PERFORMANCE_SCHEMA = 'PERFORMANCE_SCHEMA';

  /**
   * The default storage engine in MySQL 5.5 and later releases.
   */
  case INNO_DB = 'InnoDB';

  /**
   * Hash-based, stored in memory storage engine, useful for temporary tables.
   */
  case MEMORY = 'MEMORY';

  /**
   * A storage engine that stores values in comma-separated values (CSV) format.
   */
  case CSV = 'CSV';

  /**
   * A brief descriptions of the storage engine.
   * @return string Returns a brief description of the storage engine.
   */
  public function comment(): string
  {
    return match ($this) {
      self::ARCHIVE => 'Archive storage engine',
      self::BLACKHOLE => '/dev/null storage engine (anything you write to it disappears)',
      self::MRG_MYISAM => 'Collection of identical MyISAM tables',
      self::FEDERATED => 'Federated MySQL storage engine',
      self::MY_ISAM => 'MyISAM storage engine',
      self::PERFORMANCE_SCHEMA => 'Performance Schema',
      self::INNO_DB => 'Supports transactions, row-level locking, and foreign keys',
      self::MEMORY => 'Hash based, stored in memory, useful for temporary tables',
      self::CSV => 'CSV storage engine',
    };
  }

  /**
   * Specifies whether the storage engine supports transactions.
   * @return bool|null Returns true if the storage engine supports transactions, false if not and null if not applicable.
   */
  public function hasTransactionSupport(): ?bool
  {
    return match ($this) {
      self::ARCHIVE,
      self::BLACKHOLE,
      self::MRG_MYISAM,
      self::MY_ISAM,
      self::PERFORMANCE_SCHEMA,
      self::MEMORY,
      self::CSV => false,
      self::FEDERATED => null,
      self::INNO_DB => true,
    };
  }

  /**
   * Specifies whether the storage engine supports XA transactions.
   * @return bool|null Returns true if the storage engine supports XA transactions, false if not and null if not
   * applicable.
   */
  public function hasXASupport(): ?bool
  {
    return match ($this) {
      self::ARCHIVE,
      self::BLACKHOLE,
      self::MRG_MYISAM,
      self::MY_ISAM,
      self::PERFORMANCE_SCHEMA,
      self::MEMORY,
      self::CSV => false,
      self::FEDERATED => null,
      self::INNO_DB => true,
    };
  }

  /**
   * Specifies whether the storage engine supports save-points.
   * @return bool|null Returns true if the storage engine supports save-points, false if not and null if not applicable.
   */
  public function hasSavepointsSupport(): ?bool
  {
    return match ($this) {
      self::ARCHIVE,
      self::BLACKHOLE,
      self::MRG_MYISAM,
      self::MY_ISAM,
      self::PERFORMANCE_SCHEMA,
      self::MEMORY,
      self::CSV => false,
      self::FEDERATED => null,
      self::INNO_DB => true,
    };
  }
}
