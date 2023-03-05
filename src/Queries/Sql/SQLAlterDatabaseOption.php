<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Traits\ExecutableTrait;

/**
 * The SQLAlterDatabaseOption class provides a set of methods for defining the options that define a particular
 * database. It includes the setCharacterSet, setDefaultCollation, setEncryption, and setReadOnly methods for
 * setting the database's character set, default collation, encryption status, and read-only status, respectively.
 * The class uses the ExecutableTrait to enable query execution. The class is marked as final, meaning
 * it cannot be extended.
 */
final class SQLAlterDatabaseOption
{
  use ExecutableTrait;

  /**
   * Constructs the SQLDatabaseOption
   *
   * @param SQLQuery $query
   */
  public function __construct(private readonly SQLQuery $query)
  {
  }

  /**
   * @param SQLCharacterSet $characterSetName
   * @return $this
   */
  public function setCharacterSet(SQLCharacterSet $characterSetName): SQLAlterDatabaseOption
  {
    $this->query->appendQueryString(tail: "DEFAULT CHARACTER SET = $characterSetName->value");
    return $this;
  }

  /**
   * @param string $collationName
   * @return $this
   */
  public function setDefaultCollation(string $collationName): SQLAlterDatabaseOption
  {
    $this->query->appendQueryString(tail: "DEFAULT COLLATE = $collationName");
    return $this;
  }

  /**
   * @param bool $isEncrypted
   * @return $this
   */
  public function setEncryption(bool $isEncrypted): SQLAlterDatabaseOption
  {
    $value = $isEncrypted ? 'Y' : 'N';
    $this->query->appendQueryString(tail: "DEFAULT ENCRYPTION = $value");
    return $this;
  }

  /**
   * @param bool $isReadOnly
   * @return $this
   */
  public function setReadOnly(bool $isReadOnly): SQLAlterDatabaseOption
  {
    $value = $isReadOnly ? '1' : '0';
    $this->query->appendQueryString(tail: "READ ONLY = DEFAULT $value");
    return $this;
  }
}