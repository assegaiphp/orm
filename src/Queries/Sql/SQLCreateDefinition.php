<?php

namespace Assegai\Orm\Queries\Sql;

final class SQLCreateDefinition
{
  public function __construct(protected SQLQuery $query) {}

  /**
   * @param string $tableName
   * @param bool $isTemporary
   * @param bool $checkIfNotExists
   * @return SQLCreateTableStatement
   */
  public function table(
    string $tableName,
    bool $isTemporary = false,
    bool $checkIfNotExists = true
  ): SQLCreateTableStatement
  {
    return new SQLCreateTableStatement(
      query: $this->query,
      tableName: $tableName,
      isTemporary: $isTemporary,
      checkIfNotExists: $checkIfNotExists
    );
  }

  /**
   * @param string $dbName
   * @param string $defaultCharacterSet
   * @param string $defaultCollation
   * @param bool $defaultEncryption
   * @return SQLCreateDatabaseStatement
   */
  public function database(
    string $dbName,
    string $defaultCharacterSet = 'utf8mb4',
    string $defaultCollation = 'utf8mb4_general_ci',
    bool $defaultEncryption = true,
  ): SQLCreateDatabaseStatement
  {
    return new SQLCreateDatabaseStatement(
      query: $this->query,
      dbName: $dbName,
      defaultCharacterSet: $defaultCharacterSet,
      defaultCollation: $defaultCollation,
      defaultEncryption: $defaultEncryption
    );
  }

  /**
   * @return SQLQueryResult
   */
  public function execute(): SQLQueryResult
  {
    return $this->query->execute();
  }
}
