<?php

namespace Assegaiphp\Orm\Queries\Sql;

use Assegaiphp\Orm\Traits\ExecutableTrait;

final class SQLCreateDatabaseStatement
{
  use ExecutableTrait;

  private string $queryString = '';

  /**
   * @param SQLQuery $query
   * @param string $dbName
   * @param string $defaultCharacterSet
   * @param string $defaultCollation
   * @param bool $defaultEncryption
   * @param bool $checkIfNotExists
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string   $dbName,
    private readonly string   $defaultCharacterSet = 'utf8mb4',
    private readonly string   $defaultCollation = 'utf8mb4_general_ci',
    private readonly bool     $defaultEncryption = true,
    private readonly bool $checkIfNotExists = true
  )
  {
    $this->queryString = "CREATE DATABASE ";
    if ($checkIfNotExists)
    {
      $this->queryString .= "IF NOT EXISTS ";
    }
    $this->queryString .= "$dbName ";
    if (!empty($defaultCharacterSet))
    {
      $this->queryString .= "CHARACTER SET $defaultCharacterSet ";
    }
    
    if (!empty($defaultCollation))
    {
      $this->queryString .= "COLLATE $defaultCollation ";
    }
    
    if ($defaultEncryption)
    {
      $this->queryString .= "ENCRYPTION 'Y' ";
    }

    $this->queryString = trim($this->queryString);
    $this->query->setQueryString(queryString: $this->queryString);
  }
}
