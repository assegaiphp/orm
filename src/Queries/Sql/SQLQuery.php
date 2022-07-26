<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Core\Config;
use Assegai\Orm\Exceptions\ORMException;
use PDO;
use PDOException;
use stdClass;

final class SQLQuery
{
  private string $queryString;
  private string $type;
  private array $params;
  private ?int $lastInsertId = null;
  private ?int $rowCount = null;

  /**
   * @param PDO $db
   * @param string $fetchClass
   * @param int $fetchMode
   * @param array $fetchClassParams
   * @param array $passwordHashFields
   * @param string $passwordHashAlgorithm
   */
  public function __construct(
    private readonly PDO   $db,
    private readonly string $fetchClass = stdClass::class,
    private readonly int    $fetchMode = PDO::FETCH_ASSOC,
    private readonly array  $fetchClassParams = [],
    private readonly array  $passwordHashFields = ['password'],
    private string          $passwordHashAlgorithm = ''
  ) {
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
   * @return void
   */
  public function init(): void
  {
    $this->queryString = '';
    $this->type = '';
    $this->params = [];
  }

  /**
   * @return array|string[]
   */
  public function passwordHashFields(): array
  {
    return $this->passwordHashFields;
  }

  /**
   * @return string
   */
  public function passwordHashAlgorithm(): string
  {
    return $this->passwordHashAlgorithm;
  }

  /**
   * @return int|null
   */
  public function lastInsertId(): ?int
  {
    return $this->lastInsertId;
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    return $this->queryString;
  }

  /**
   * @return string Returns the raw Sql query as a string.
   */
  public function queryString(): string
  {
    return $this->queryString;
  }

  /**
   * @return string
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
   * @throws ORMException
   */
  public function execute(): SQLQueryResult
  {
    try
    {
      $statement = $this->db->prepare($this->queryString);

      $statement->execute($this->params);

      if (!empty($statement->errorInfo()))
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
        }
  
        return new SQLQueryResult(data: $data, errors: [], isOK: true);
      }

      $errors = [
        'code' => $this->db->errorCode(),
        'info' => $this->db->errorInfo(),
      ];

      return new SQLQueryResult( data: [], errors: $errors, isOK: false );
    }
    catch (PDOException)
    {
      list($sqlCode, $driverCode, $message) = $statement->errorInfo();
      if (Config::environment('ENVIRONMENT') === 'PROD')
      {
        $message = 'Bad Request';
      }
      throw match($sqlCode) {
        '23000' => new ORMException(message: "$driverCode - $message"),
        default => new ORMException(message: "General SQL error - $message")
      };
    }
  }

  /**
   * @return never
   */
  public function debug(): never
  {
    exit($this . PHP_EOL);
  }
}