<?php

namespace Assegai\Orm\Interfaces;

use PDO;
use PDOStatement;

/**
 * Interface declaration for the IDataObject interface
 */
interface IDataObject
{
  /**
   * Initiates a transaction
   *
   * @return bool Returns true on success or false on failure
   */
  public function beginTransaction(): bool;

  /**
   * Commits a transaction
   *
   * @return bool Returns true on success or false on failure
   */
  public function commit(): bool;

  /**
   * Retrieves the error code associated with the last operation
   *
   * @return ?string Returns the error code as a string or null if no error occurred
   */
  public function errorCode(): ?string;

  /**
   * Retrieves an array of error information associated with the last operation
   *
   * @return array Returns an array of error information as strings
   */
  public function errorInfo(): array;

  /**
   * Executes an SQL statement in a single function call
   *
   * @param string $statement The SQL statement to execute
   *
   * @return int|false Returns the number of rows affected by the last DELETE, INSERT, or UPDATE statement, or false on failure
   */
  public function exec(string $statement): int|false;

  /**
   * Retrieves an attribute
   *
   * @param int $attribute The attribute to retrieve
   *
   * @return mixed Returns the value of the requested attribute
   */
  public function getAttribute(int $attribute): mixed;

  /**
   * Returns an array of available PDO drivers
   *
   * @return array Returns an array of PDO driver names
   */
  public static function getAvailableDrivers(): array;

  /**
   * Determines whether a transaction is currently active
   *
   * @return bool Returns true if a transaction is active, false otherwise
   */
  public function inTransaction(): bool;

  /**
   * Returns the ID of the last inserted row or sequence value
   *
   * @param ?string $name The name of the sequence object from which the ID should be returned
   *
   * @return string|false Returns the ID of the last inserted row or sequence value as a string, or false on failure
   */
  public function lastInsertId(?string $name = null): string|false;

  /**
   * Prepares an SQL statement for execution
   *
   * @param string $query The SQL statement to prepare
   * @param array $options A set of key/value pairs representing attributes for the prepared statement
   *
   * @return PDOStatement|false Returns a PDOStatement object on success or false on failure
   */
  public function prepare(string $query, array $options = []): PDOStatement|false;

  /**
   * Executes an SQL statement and returns a result set as a PDOStatement object
   *
   * @param string $query The SQL statement to execute
   * @param ?int $fetchMode The fetch mode to use when retrieving the result set
   * @param int|string|object $colnoOrClassnameOrObject The argument to pass to the fetch mode
   * @param array $constructorArgs Arguments to pass to the constructor of the class specified by the fetch mode
   *
   * @return PDOStatement|false Returns a PDOStatement object on success or false on failure
   */
  public function query(
      string $query,
      ?int $fetchMode = null,
      int|string|object $colnoOrClassnameOrObject = '',
      array $constructorArgs = []
  ): PDOStatement|false;

  /**
   * Quotes a string for use in a query.
   *
   * @param string $string The string to quote.
   * @param int $type One of the PDO::PARAM_* constants.
   *
   * @return string|false The quoted string, or false on failure.
   */
  public function quote(string $string, int $type = PDO::PARAM_STR): string|false;

  /**
   * Rolls back the current transaction.
   *
   * @return bool True on success, false on failure.
   */
  public function rollBack(): bool;

  /**
   * Sets an attribute on the database handle.
   *
   * @param int $attribute The attribute to set.
   * @param mixed $value The value to set.
   *
   * @return bool True on success, false on failure.
   */
  public function setAttribute(int $attribute, mixed $value): bool;
}