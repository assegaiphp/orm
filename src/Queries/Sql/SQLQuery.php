<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Core\Config;
use Assegai\Orm\Exceptions\ORMException;
use PDO;
use PDOException;
use stdClass;

/**
 * Class SQLQuery. Represents a SQL query.
 */
final class SQLQuery
{
  /**
   * @var string The raw SQL query as a string.
   */
  private string $queryString;
  /**
   * @var string The type of the query.
   */
  private string $type;
  /**
   * @var array The parameters of the query.
   */
  private array $params;
  /**
   * @var int|null The last inserted id.
   */
  private ?int $lastInsertId = null;
  /**
   * @var int|null The number of rows affected by the query.
   */
  private ?int $rowCount = null;

  /**
   * Constructs a new SQLQuery instance.
   *
   * @param PDO $db The PDO instance.
   * @param string $fetchClass The class to use for fetching data.
   * @param int $fetchMode The fetch mode to use.
   * @param array $fetchClassParams The parameters to pass to the fetch class.
   * @param array $passwordHashFields The fields to hash.
   * @param string $passwordHashAlgorithm The algorithm to use for hashing.
   */
  public function __construct(
    private readonly PDO   $db,
    private readonly string $fetchClass = stdClass::class,
    private readonly int    $fetchMode = PDO::FETCH_ASSOC,
    private readonly array  $fetchClassParams = [],
    private readonly array  $passwordHashFields = ['password'],
    private string          $passwordHashAlgorithm = ''
  )
  {
    if (empty($this->passwordHashAlgorithm))
    {
      $this->passwordHashAlgorithm = Config::get('default_password_hash_algo') ?? '2y';

      if (empty($this->passwordHashAlgorithm))
      {
        $this->passwordHashAlgorithm = PASSWORD_DEFAULT;
      }  
    }
    $this->init();
  }

  /**
   * Returns the connection to the database.
   *
   * @return PDO Returns the connection to the database.
   */
  public function getConnection(): PDO
  {
    return $this->db;
  }

  /**
   * Initializes the query.
   *
   * @return void
   */
  public function init(): void
  {
    $this->queryString = '';
    $this->type = '';
    $this->params = [];
  }

  /**
   * Returns the list of fields to hash.
   *
   * @return array|string[] The list of fields to hash.
   */
  public function passwordHashFields(): array
  {
    return $this->passwordHashFields;
  }

  /**
   * Returns the algorithm to use for hashing.
   *
   * @return string The algorithm to use for hashing.
   */
  public function passwordHashAlgorithm(): string
  {
    return $this->passwordHashAlgorithm;
  }

  /**
   * Returns the ID of the last inserted row or sequence value.
   *
   * @return int|null The ID of the last inserted row or sequence value.
   */
  public function lastInsertId(): ?int
  {
    return $this->lastInsertId;
  }

  /**
   * Returns a string representing the query.
   *
   * @return string Returns a string representing the query.
   */
  public function __toString(): string
  {
    return $this->queryString;
  }

  /**
   * Returns the raw Sql query as a string.
   *
   * @return string Returns the raw Sql query as a string.
   */
  public function queryString(): string
  {
    return $this->queryString;
  }

  /**
   * Returns the type of the query.
   *
   * @return string Returns the type of the query.
   */
  public function type(): string
  {
    return $this->type;
  }

  /**
   * @return int|null
   */
  public function rowCount(): ?int
  {
    return $this->rowCount;
  }

  /**
   * @param string $queryString
   * @return void
   */
  public function setQueryString(string $queryString): void
  {
    $this->queryString = $queryString;
  }

  /**
   * @param string $tail
   * @return void
   */
  public function appendQueryString(string $tail): void
  {
    $this->queryString = trim($this->queryString) . " $tail";
  }

  /**
   * @return SQLAlterDefinition
   */
  public function alter(): SQLAlterDefinition
  {
    return new SQLAlterDefinition( query: $this );
  }

  /**
   * @return SQLCreateDefinition
   */
  public function create(): SQLCreateDefinition
  {
    $this->type = SQLQueryType::CREATE;
    return new SQLCreateDefinition( query: $this );
  }

  /**
   * @return SQLDropDefinition
   */
  public function drop(): SQLDropDefinition
  {
    $this->type = SQLQueryType::DROP;
    return new SQLDropDefinition( query: $this );
  }

  /**
   * @return SQLRenameStatement
   */
  public function rename(): SQLRenameStatement
  {
    return new SQLRenameStatement( query: $this );
  }

  /**
   * @param string $dbName
   * @return SQLUseStatement
   */
  public function use(string $dbName): SQLUseStatement
  {
    $this->type = SQLQueryType::USE;
    return new SQLUseStatement( query: $this, dbName: $dbName );
  }

  /**
   * @param string $subject
   * @return SQLDescribeStatement
   */
  public function describe(string $subject): SQLDescribeStatement
  {
    $this->type = SQLQueryType::DESCRIBE;
    return new SQLDescribeStatement( query: $this, subject: $subject );
  }

  /**
   * @param string $tableName
   * @return SQLInsertIntoDefinition
   */
  public function insertInto(string $tableName): SQLInsertIntoDefinition
  {
    $this->type = SQLQueryType::INSERT;
    return new SQLInsertIntoDefinition( query: $this, tableName: $tableName );
  }

  /**
   * @param string $tableName
   * @param bool $lowPriority
   * @param bool $ignore
   * @return SQLUpdateDefinition
   */
  public function update(
    string $tableName,
    bool $lowPriority = false,
    bool $ignore = false,
  ): SQLUpdateDefinition
  {
    $this->type = SQLQueryType::UPDATE;
    return new SQLUpdateDefinition(
      query: $this,
      tableName: $tableName,
      lowPriority: $lowPriority,
      ignore: $ignore
    );
  }

  /**
   * @return SQLSelectDefinition
   */
  public function select(): SQLSelectDefinition
  {
    $this->type = SQLQueryType::SELECT;
    return new SQLSelectDefinition( query: $this );
  }

  /**
   * @param string $tableName
   * @param string|null $alias
   * @return SQLDeleteFromStatement
   */
  public function deleteFrom(string $tableName, ?string $alias = null): SQLDeleteFromStatement
  {
    $this->type = SQLQueryType::DELETE;
    return new SQLDeleteFromStatement(
      query: $this,
      tableName: $tableName,
      alias: $alias
    );
  }

  /**
   * @param string $tableName
   * @return SQLTruncateStatement
   */
  public function truncateTable(string $tableName): SQLTruncateStatement
  {
    $this->type = SQLQueryType::TRUNCATE;
    return new SQLTruncateStatement( query: $this, tableName: $tableName );
  }

  /**
   * @return SQLQueryResult
   */
  public function execute(): SQLQueryResult
  {
    try
    {
      $statement = $this->db->prepare($this->queryString);

      if ($statement->execute($this->params))
      {
        if (!empty($this->params))
        {
          call_user_func_array([$statement, 'setFetchMode'], $this->fetchClassParams );
        }
  
        $data = match ($this->type()) {
          SQLQueryType::SELECT => $statement->fetchAll(mode: $this->fetchMode),
          default => $statement->fetchAll()
        };

        if ($this->type() === SQLQueryType::INSERT)
        {
          $this->lastInsertId = $this->db->lastInsertId();
          if ($this->lastInsertId && isset($data['id']))
          {
            $data['id'] = $this->lastInsertId;
          }
        }

        return new SQLQueryResult(data: $data, errors: [], raw: $this->queryString, affected: $statement->rowCount());
      }

      $errors = [
        'code' => $this->db->errorCode(),
        'info' => $this->db->errorInfo(),
      ];

      return new SQLQueryResult( data: [], errors: $errors, raw: $this->queryString );
    }
    catch (PDOException)
    {
      [$sqlCode, $driverCode, $message] = $statement->errorInfo();
      if (Config::environment() === 'PROD')
      {
        $message = 'Bad Request';
      }
      $errors[] = match($sqlCode) {
        '23000' => new ORMException(message: "$driverCode - $message"),
        default => new ORMException(message: "General SQL error - $message")
      };

      return new SQLQueryResult( data: [], errors: $errors, raw: $this->queryString );
    }
  }

  /**
   * Exit the script and print the query.
   *
   * @return never
   */
  public function debug(): never
  {
    exit($this . PHP_EOL);
  }
}