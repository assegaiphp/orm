<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Support\OrmRuntime;
use Assegai\Orm\Util\SqlIdentifier;
use Assegai\Orm\Util\SqlDialectHelper;
use DateTimeInterface;
use PDO;
use PDOException;
use stdClass;
use UnitEnum;

/**
 * Class SQLQuery. Represents a SQL query.
 */
class SQLQuery
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
     * @var int|null The number of columns affected by the query.
     */
    private ?int $columnCount = null;
    /**
     * @var SQLDialect The SQL dialect used for rendering queries.
     */
    private readonly SQLDialect $dialect;

    /**
     * Constructs a new SQLQuery instance.
     *
     * @param PDO $db The PDO instance.
     * @param string $fetchClass The class to use for fetching data.
     * @param int $fetchMode The fetch mode to use.
     * @param array $fetchClassParams The parameters to pass to the fetch class.
     * @param array $passwordHashFields The fields to hash.
     * @param string $passwordHashAlgorithm The algorithm to use for hashing.
     * @param SQLDialect|null $dialect The SQL dialect to render queries for.
     */
    public function __construct(
        private readonly PDO    $db,
        private readonly string $fetchClass = stdClass::class,
        private readonly int    $fetchMode = PDO::FETCH_ASSOC,
        private readonly array  $fetchClassParams = [],
        private readonly array  $passwordHashFields = ['password'],
        private string          $passwordHashAlgorithm = '',
        ?SQLDialect             $dialect = null
    )
    {
        $this->dialect = $dialect ?? SqlDialectHelper::fromPdo($db);
        if (empty($this->passwordHashAlgorithm)) {
            $this->passwordHashAlgorithm = OrmRuntime::defaultPasswordHashAlgorithm();

            if (empty($this->passwordHashAlgorithm)) {
                $this->passwordHashAlgorithm = PASSWORD_DEFAULT;
            }
        }
        $this->init();
    }
    /**
     * Creates the appropriate root query builder for the given connection and dialect.
     *
     * @param PDO $db
     * @param string $fetchClass
     * @param int $fetchMode
     * @param array $fetchClassParams
     * @param array $passwordHashFields
     * @param string $passwordHashAlgorithm
     * @param SQLDialect|null $dialect
     * @return self
     */
    public static function forConnection(
        PDO $db,
        string $fetchClass = stdClass::class,
        int $fetchMode = PDO::FETCH_ASSOC,
        array $fetchClassParams = [],
        array $passwordHashFields = ['password'],
        string $passwordHashAlgorithm = '',
        ?SQLDialect $dialect = null,
    ): self
    {
        $resolvedDialect = $dialect ?? SqlDialectHelper::fromPdo($db);

        return match ($resolvedDialect) {
            SQLDialect::POSTGRESQL => new \Assegai\Orm\Queries\PostgreSql\PostgreSQLQuery(
                db: $db,
                fetchClass: $fetchClass,
                fetchMode: $fetchMode,
                fetchClassParams: $fetchClassParams,
                passwordHashFields: $passwordHashFields,
                passwordHashAlgorithm: $passwordHashAlgorithm,
                dialect: $resolvedDialect,
            ),
            SQLDialect::SQLITE => new \Assegai\Orm\Queries\SQLite\SQLiteQuery(
                db: $db,
                fetchClass: $fetchClass,
                fetchMode: $fetchMode,
                fetchClassParams: $fetchClassParams,
                passwordHashFields: $passwordHashFields,
                passwordHashAlgorithm: $passwordHashAlgorithm,
                dialect: $resolvedDialect,
            ),
            SQLDialect::MARIADB => new \Assegai\Orm\Queries\MariaDb\MariaDbQuery(
                db: $db,
                fetchClass: $fetchClass,
                fetchMode: $fetchMode,
                fetchClassParams: $fetchClassParams,
                passwordHashFields: $passwordHashFields,
                passwordHashAlgorithm: $passwordHashAlgorithm,
                dialect: $resolvedDialect,
            ),
            default => new \Assegai\Orm\Queries\MySql\MySQLQuery(
                db: $db,
                fetchClass: $fetchClass,
                fetchMode: $fetchMode,
                fetchClassParams: $fetchClassParams,
                passwordHashFields: $passwordHashFields,
                passwordHashAlgorithm: $passwordHashAlgorithm,
                dialect: $resolvedDialect,
            ),
        };
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
        $this->lastInsertId = null;
        $this->rowCount = null;
        $this->columnCount = null;
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
     * Returns the SQL dialect used for query rendering.
     *
     * @return SQLDialect
     */
    public function getDialect(): SQLDialect
    {
        return $this->dialect;
    }
    /**
     * Returns a query instance configured for the given SQL dialect.
     *
     * @param SQLDialect $dialect
     * @return self
     */
    public function withDialect(SQLDialect $dialect): self
    {
        if ($this->dialect === $dialect) {
            return $this;
        }

        return self::forConnection(
            db: $this->db,
            fetchClass: $this->fetchClass,
            fetchMode: $this->fetchMode,
            fetchClassParams: $this->fetchClassParams,
            passwordHashFields: $this->passwordHashFields,
            passwordHashAlgorithm: $this->passwordHashAlgorithm,
            dialect: $dialect,
        );
    }

    /**
     * Returns a query configured for PostgreSQL rendering.
     *
     * @return \Assegai\Orm\Queries\PostgreSql\PostgreSQLQuery
     */
    public function switchToPostgres(): \Assegai\Orm\Queries\PostgreSql\PostgreSQLQuery
    {
        return \Assegai\Orm\Queries\PostgreSql\PostgreSQLQuery::forConnection(
            db: $this->db,
            fetchClass: $this->fetchClass,
            fetchMode: $this->fetchMode,
            fetchClassParams: $this->fetchClassParams,
            passwordHashFields: $this->passwordHashFields,
            passwordHashAlgorithm: $this->passwordHashAlgorithm,
            dialect: SQLDialect::POSTGRESQL,
        );
    }

    /**
     * Returns a query configured for PostgreSQL rendering.
     *
     * @return \Assegai\Orm\Queries\PostgreSql\PostgreSQLQuery
     */
    public function switchToPostgreSql(): \Assegai\Orm\Queries\PostgreSql\PostgreSQLQuery
    {
        return $this->switchToPostgres();
    }

    /**
     * Returns a query configured for MySQL rendering.
     *
     * @return \Assegai\Orm\Queries\MySql\MySQLQuery
     */
    public function switchToMysql(): \Assegai\Orm\Queries\MySql\MySQLQuery
    {
        return \Assegai\Orm\Queries\MySql\MySQLQuery::forConnection(
            db: $this->db,
            fetchClass: $this->fetchClass,
            fetchMode: $this->fetchMode,
            fetchClassParams: $this->fetchClassParams,
            passwordHashFields: $this->passwordHashFields,
            passwordHashAlgorithm: $this->passwordHashAlgorithm,
            dialect: SQLDialect::MYSQL,
        );
    }

    /**
     * Returns a query configured for MariaDB rendering.
     *
     * @return \Assegai\Orm\Queries\MariaDb\MariaDbQuery
     */
    public function switchToMariaDb(): \Assegai\Orm\Queries\MariaDb\MariaDbQuery
    {
        return \Assegai\Orm\Queries\MariaDb\MariaDbQuery::forConnection(
            db: $this->db,
            fetchClass: $this->fetchClass,
            fetchMode: $this->fetchMode,
            fetchClassParams: $this->fetchClassParams,
            passwordHashFields: $this->passwordHashFields,
            passwordHashAlgorithm: $this->passwordHashAlgorithm,
            dialect: SQLDialect::MARIADB,
        );
    }

    /**
     * Returns a query configured for SQLite rendering.
     *
     * @return \Assegai\Orm\Queries\SQLite\SQLiteQuery
     */
    public function switchToSqlite(): \Assegai\Orm\Queries\SQLite\SQLiteQuery
    {
        return \Assegai\Orm\Queries\SQLite\SQLiteQuery::forConnection(
            db: $this->db,
            fetchClass: $this->fetchClass,
            fetchMode: $this->fetchMode,
            fetchClassParams: $this->fetchClassParams,
            passwordHashFields: $this->passwordHashFields,
            passwordHashAlgorithm: $this->passwordHashAlgorithm,
            dialect: SQLDialect::SQLITE,
        );
    }
    /**
     * Quotes an SQL identifier for the current query dialect.
     *
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier(string $identifier): string
    {
        return SqlIdentifier::quote($identifier, $this->dialect);
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
     * Marks the current query with the provided query type.
     *
     * @param string $type
     * @return void
     */
    protected function setQueryType(string $type): void
    {
        $this->type = $type;
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
     * Replaces the conditions in the WHERE clause of the query.
     *
     * @param string $replacementConditions The conditions to replace the WHERE clause with.
     * @return SQLQuery The current instance.
     */
    public function replaceWhereClause(string $replacementConditions): self
    {
        $this->queryString = preg_replace('/\bWHERE\b\s+(.*?)(?=\b(ORDER BY|LIMIT|OFFSET|GROUP BY|HAVING|$)\b)/is', 'WHERE ' . $replacementConditions, $this->queryString);
        return $this;
    }

    /**
     * @return SQLAlterDefinition
     */
    public function alter(): SQLAlterDefinition
    {
        $this->init();
        return new SQLAlterDefinition(query: $this);
    }

    /**
     * @return SQLCreateDefinition
     */
    public function create(): SQLCreateDefinition
    {
        $this->init();
        $this->type = SQLQueryType::CREATE;
        return new SQLCreateDefinition(query: $this);
    }

    /**
     * @return SQLDropDefinition
     */
    public function drop(): SQLDropDefinition
    {
        $this->init();
        $this->type = SQLQueryType::DROP;
        return new SQLDropDefinition(query: $this);
    }

    /**
     * @return SQLRenameStatement
     */
    public function rename(): SQLRenameStatement
    {
        $this->init();
        return new SQLRenameStatement(query: $this);
    }

    /**
     * @param string $dbName
     * @return SQLUseStatement
     */
    public function use(string $dbName): SQLUseStatement
    {
        $this->init();
        $this->type = SQLQueryType::USE;
        return new SQLUseStatement(query: $this, dbName: $dbName);
    }

    /**
     * @param string $subject
     * @return SQLDescribeStatement
     */
    public function describe(string $subject): SQLDescribeStatement
    {
        $this->init();
        $this->type = SQLQueryType::DESCRIBE;
        return new SQLDescribeStatement(query: $this, subject: $subject);
    }

    /**
     * @param string $tableName
     * @return SQLInsertIntoDefinition
     */
    public function insertInto(string $tableName): SQLInsertIntoDefinition
    {
        $this->init();
        $this->type = SQLQueryType::INSERT;
        return new SQLInsertIntoDefinition(query: $this, tableName: $tableName);
    }

    /**
     * @param string $tableName
     * @param bool $lowPriority
     * @param bool $ignore
     * @return SQLUpdateDefinition
     */
    public function update(string $tableName, bool $lowPriority = false, bool $ignore = false): SQLUpdateDefinition
    {
        $this->init();
        $this->type = SQLQueryType::UPDATE;
        return new SQLUpdateDefinition(query: $this, tableName: $tableName, lowPriority: $lowPriority, ignore: $ignore);
    }

    /**
     * @return SQLSelectDefinition
     */
    public function select(): SQLSelectDefinition
    {
        $this->init();
        $this->type = SQLQueryType::SELECT;
        return new SQLSelectDefinition(query: $this);
    }

    /**
     * @param string $tableName
     * @param string|null $alias
     * @return SQLDeleteFromStatement
     */
    public function deleteFrom(string $tableName, ?string $alias = null): SQLDeleteFromStatement
    {
        $this->init();
        $this->type = SQLQueryType::DELETE;
        return new SQLDeleteFromStatement(query: $this, tableName: $tableName, alias: $alias);
    }

    /**
     * @param string $tableName
     * @return SQLTruncateStatement
     */
    public function truncateTable(string $tableName): SQLTruncateStatement
    {
        $this->init();
        $this->type = SQLQueryType::TRUNCATE;
        return new SQLTruncateStatement(query: $this, tableName: $tableName);
    }

    /**
     * Adds a bound parameter to the current query and returns its placeholder.
     *
     * @param mixed $value
     * @return string
     */
    public function addParam(mixed $value): string
    {
        $this->params[] = $this->normalizeParamValue($value);

        return '?';
    }

    /**
     * @param list<mixed> $values
     * @return list<string>
     */
    public function addParams(array $values): array
    {
        return array_map(fn(mixed $value): string => $this->addParam($value), $values);
    }

    /**
     * Normalizes framework values into PDO-friendly scalars.
     *
     * @param mixed $value
     * @return mixed
     */
    private function normalizeParamValue(mixed $value): mixed
    {
        if ($value instanceof UnitEnum && property_exists($value, 'value')) {
            return $value->value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return match (true) {
            is_bool($value) => (int)$value,
            $value instanceof stdClass, is_array($value) => json_encode($value),
            default => $value,
        };
    }

    /**
     * @return SQLQueryResult
     */
    public function execute(): SQLQueryResult
    {
        try {
            $statement = $this->db->prepare($this->queryString);

            if (false === $statement) {
                return new SQLQueryResult(data: [], errors: [new ORMException("Failed to prepare the SQL statement.")], raw: $this->queryString);
            }

            if ($statement->execute($this->params)) {
                [$sqlCode, $driverCode, $message] = $statement->errorInfo();
                if ($sqlCode !== '00000') {
                    $errors[] = match ($sqlCode) {
                        '23000' => new ORMException(message: "$driverCode - $message"),
                        default => new ORMException(message: "General SQL error - $message")
                    };

                    return new SQLQueryResult(data: [], errors: $errors, raw: $this->queryString);
                }
                if (!empty($this->fetchClassParams)) {
                    call_user_func_array([$statement, 'setFetchMode'], $this->fetchClassParams);
                }

                $data = match ($this->type()) {
                    SQLQueryType::SELECT => $statement->fetchAll(mode: $this->fetchMode),
                    default => $this->queryReturnsRows()
                        ? $statement->fetchAll(mode: PDO::FETCH_ASSOC)
                        : []
                };

                if ($this->type() === SQLQueryType::INSERT) {
                    $this->lastInsertId = $this->resolveLastInsertId($data);
                }

                $this->rowCount = $statement->rowCount();
                $this->columnCount = $statement->columnCount();

                return new SQLQueryResult(data: $data, errors: [], raw: $this->queryString, affected: $statement->rowCount());
            }

            $errors = ['code' => $this->db->errorCode(), 'info' => $this->db->errorInfo(),];

            return new SQLQueryResult(data: [], errors: $errors, raw: $this->queryString);
        } catch (PDOException $exception) {
            $message = $exception->getMessage();

            if (OrmRuntime::isProduction()) {
                $message = "Internal server error.";
            }

            $errors = [
                new ORMException(message: "General SQL error - $message"),
                $exception,
            ];

            return new SQLQueryResult(data: [], errors: $errors, raw: $this->queryString);
        }
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
     * Returns the ID of the last inserted row or sequence value.
     *
     * @return int|null The ID of the last inserted row or sequence value.
     */
    public function lastInsertId(): ?int
    {
        return $this->lastInsertId;
    }

    /**
     * @return int|null
     */
    public function rowCount(): ?int
    {
        return $this->rowCount;
    }

    /**
     * @return int|null
     */
    public function columnCount(): ?int
    {
        return $this->columnCount;
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

    private function queryReturnsRows(): bool
    {
        return str_contains(strtoupper($this->queryString), 'RETURNING ');
    }

    private function resolveLastInsertId(array $data): ?int
    {
        $lastInsertId = $this->safeLastInsertId();

        if ($lastInsertId !== null && $lastInsertId > 0) {
            return $lastInsertId;
        }

        return $this->resolveLastInsertIdFromReturningData($data);
    }

    private function safeLastInsertId(): ?int
    {
        try {
            $lastInsertId = $this->db->lastInsertId();
        } catch (PDOException) {
            return null;
        }

        if (!is_numeric($lastInsertId)) {
            return null;
        }

        $lastInsertId = (int) $lastInsertId;

        return $lastInsertId > 0 ? $lastInsertId : null;
    }

    private function resolveLastInsertIdFromReturningData(array $data): ?int
    {
        $firstRow = $data[0] ?? null;

        if (!is_array($firstRow)) {
            return null;
        }

        foreach (['id', 'Id'] as $key) {
            if (isset($firstRow[$key]) && is_numeric($firstRow[$key])) {
                return (int) $firstRow[$key];
            }
        }

        foreach ($firstRow as $value) {
            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }
}
