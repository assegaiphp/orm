<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\DataSourceConnectionException;
use Assegai\Orm\Support\OrmRuntime;
use Assegai\Orm\Util\SqlDialectHelper;
use PDO;
use PDOException;

/**
 * The `DBFactory` class houses static methods for creating **Database
 * connection objects**.
 */
final class DBFactory
{
    /**
     * @var array|array[]
     */
    private static array $connections = [
        'mysql' => [],
        'mariadb' => [],
        'pgsql' => [],
        'mssql' => [],
        'sqlite' => [],
        'mongodb' => [],
    ];

    /**
     * @var array<string, array<string, int>>
     */
    private static array $sharedConnectionReferences = [
        'mysql' => [],
        'mariadb' => [],
        'pgsql' => [],
        'mssql' => [],
        'sqlite' => [],
        'mongodb' => [],
    ];

    /**
     * @param string $dbName
     * @param SQLDialect|null $dialect
     * @return PDO
     * @throws DataSourceConnectionException
     */
    public static function getSQLConnection(string $dbName, ?SQLDialect $dialect = SQLDialect::MYSQL): PDO
    {
        return match ($dialect) {
            SQLDialect::MARIADB => self::getMariaDBConnection(dbName: $dbName),
            SQLDialect::MSSQL => self::getMsSqlConnection(dbName: $dbName),
            SQLDialect::POSTGRESQL => self::getPostgresSQLConnection(dbName: $dbName),
            SQLDialect::SQLITE => self::getSQLiteConnection(dbName: $dbName),
            default => self::getMySQLConnection(dbName: $dbName)
        };
    }

    /**
     * @param string $dbName
     * @return PDO
     * @throws DataSourceConnectionException
     */
    public static function getMariaDBConnection(string $dbName): PDO
    {
        return self::getMySQLConnection(dbName: $dbName);
    }

    /**
     * @param string $dbName
     * @return PDO
     * @throws DataSourceConnectionException
     */
    public static function getMySQLConnection(string $dbName): PDO
    {
        $type = 'mysql';
        self::ensureDriverIsAvailable(SQLDialect::MYSQL);

        if (empty($dbName)) {
            throw new DataSourceConnectionException();
        }

        if (!isset(self::$connections[$type][$dbName]) || empty(self::$connections[$type][$dbName])) {
            self::validateDatabaseDetails(type: $type, dbName: $dbName);
            $config = OrmRuntime::databaseConfigs()[$type][$dbName];

            if (empty($config)) {
                $databases = OrmRuntime::databaseConfigs()[$type];

                if (!empty($databases)) {
                    $config = array_pop($databases);
                }
            }

            try {
                $options = DataSourceOptions::fromArray([
                    ...$config,
                    'name' => $config['name'] ?? $dbName,
                    'database' => $config['database'] ?? $dbName,
                    'type' => DataSourceType::MYSQL,
                ]);
                $user = $options->username ?? 'root';
                $password = $options->password ?? '';

                self::$connections[$type][$dbName] = new PDO(
                    dsn: self::buildMySqlDsn($options->host, $options->port, $options->name, $options->charSet),
                    username: $user,
                    password: $password
                );
                self::applyConnectionAttributes(self::$connections[$type][$dbName], SQLDialect::MYSQL);
            } catch (PDOException) {
                throw new DataSourceConnectionException();
            }
        }

        return self::$connections[$type][$dbName];
    }

    /**
     * @param string $type
     * @param string $dbName
     * @return void
     * @throws DataSourceConnectionException
     */
    private static function validateDatabaseDetails(string $type, string $dbName): void
    {
        $databases = OrmRuntime::databaseConfigs();

        if (!isset($databases[$type]) || !isset($databases[$type][$dbName])) {
            throw new DataSourceConnectionException(self::getDataSourceTypeForConnectionPool($type));
        }
    }

    private static function getDataSourceTypeForConnectionPool(string $type): DataSourceType
    {
        return match ($type) {
            'mariadb' => DataSourceType::MARIADB,
            'mssql' => DataSourceType::MSSQL,
            'pgsql' => DataSourceType::POSTGRESQL,
            'sqlite' => DataSourceType::SQLITE,
            'mongodb' => DataSourceType::MONGODB,
            default => DataSourceType::MYSQL,
        };
    }

    public static function buildMySqlDsn(
        string           $host,
        int              $port,
        string           $database,
        ?SQLCharacterSet $charSet = SQLCharacterSet::UTF8MB4
    ): string
    {
        $host = self::normalizeMySqlHostForDsn($host, $port);

        $segments = [
            sprintf('mysql:host=%s', $host),
            sprintf('port=%d', $port),
            sprintf('dbname=%s', $database),
        ];

        if ($charSet instanceof SQLCharacterSet) {
            $segments[] = sprintf('charset=%s', $charSet->value);
        }

        return implode(';', $segments);
    }

    private static function normalizeMySqlHostForDsn(string $host, int $port): string
    {
        if (strcasecmp($host, 'localhost') === 0 && $port > 0) {
            return '127.0.0.1';
        }

        return $host;
    }

    public static function applyConnectionAttributes(PDO $connection, SQLDialect $dialect): void
    {
        foreach (self::getDefaultPdoAttributes($dialect) as $attribute => $value) {
            $connection->setAttribute($attribute, $value);
        }

        if ($dialect === SQLDialect::SQLITE) {
            $connection->exec('PRAGMA foreign_keys = ON');
            $connection->exec('PRAGMA busy_timeout = 5000');

            if (self::shouldEnableSqliteWal($connection)) {
                $connection->exec('PRAGMA journal_mode = WAL');
                $connection->exec('PRAGMA synchronous = NORMAL');
            }
        }
    }

    public static function getDefaultPdoAttributes(SQLDialect $dialect): array
    {
        $attributes = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ];

        if (in_array($dialect, [SQLDialect::MYSQL, SQLDialect::MARIADB], true)) {
            $attributes[PDO::ATTR_EMULATE_PREPARES] = false;
        }

        return $attributes;
    }

    private static function shouldEnableSqliteWal(PDO $connection): bool
    {
        $databasePath = self::getPrimarySqliteDatabasePath($connection);

        return is_string($databasePath)
            && $databasePath !== ''
            && $databasePath !== ':memory:';
    }

    private static function getPrimarySqliteDatabasePath(PDO $connection): ?string
    {
        $statement = $connection->query('PRAGMA database_list');

        if ($statement === false) {
            return null;
        }

        $database = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();

        if (!is_array($database)) {
            return null;
        }

        $path = $database['file'] ?? null;

        return is_string($path) ? $path : null;
    }

    /**
     * @param string $dbName
     * @return PDO
     * @throws DataSourceConnectionException
     */
    public static function getPostgresSQLConnection(string $dbName): PDO
    {
        $type = 'pgsql';
        self::ensureDriverIsAvailable(SQLDialect::POSTGRESQL);

        if (empty($dbName)) {
            throw new DataSourceConnectionException();
        }

        if (!isset(self::$connections[$type][$dbName]) || empty(self::$connections[$type][$dbName])) {
            self::validateDatabaseDetails(type: $type, dbName: $dbName);
            $config = OrmRuntime::databaseConfigs()[$type][$dbName];

            try {
                $options = DataSourceOptions::fromArray([
                    ...$config,
                    'name' => $config['name'] ?? $dbName,
                    'database' => $config['database'] ?? $dbName,
                    'type' => DataSourceType::POSTGRESQL,
                ]);
                $user = $options->username ?? 'postgres';
                $password = $options->password ?? '';

                self::$connections[$type][$dbName] = new PDO(
                    dsn: self::buildPostgreSqlDsn($options->host, $options->port, $options->name),
                    username: $user,
                    password: $password
                );
                self::applyConnectionAttributes(self::$connections[$type][$dbName], SQLDialect::POSTGRESQL);
            } catch (PDOException) {
                throw new DataSourceConnectionException(DataSourceType::POSTGRESQL);
            }
        }

        return self::$connections[$type][$dbName];
    }

    public static function buildPostgreSqlDsn(string $host, int $port, string $database): string
    {
        return sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $database);
    }

    /**
     * Build the SQL Server PDO DSN for a database connection.
     *
     * @param string $host The SQL Server host name or address.
     * @param int $port The SQL Server TCP port.
     * @param string $database The database name.
     * @return string Returns the SQL Server PDO DSN.
     */
    public static function buildMsSqlDsn(string $host, int $port, string $database): string
    {
        $server = $port > 0
            ? sprintf('%s,%d', $host, $port)
            : $host;

        return sprintf(
            'sqlsrv:Server=%s;Database=%s;Encrypt=yes;TrustServerCertificate=yes',
            $server,
            $database
        );
    }

    /**
     * Retrieve or create a shared SQL Server connection from the runtime config.
     *
     * @param string $dbName The configured SQL Server database name.
     * @return PDO Returns the cached SQL Server connection.
     * @throws DataSourceConnectionException When the connection cannot be created.
     */
    public static function getMsSqlConnection(string $dbName): PDO
    {
        $type = 'mssql';
        self::ensureDriverIsAvailable(SQLDialect::MSSQL);

        if (empty($dbName)) {
            throw new DataSourceConnectionException(DataSourceType::MSSQL);
        }

        if (!isset(self::$connections[$type][$dbName]) || empty(self::$connections[$type][$dbName])) {
            self::validateDatabaseDetails(type: $type, dbName: $dbName);
            $config = OrmRuntime::databaseConfigs()[$type][$dbName];

            if (empty($config)) {
                $databases = OrmRuntime::databaseConfigs()[$type];

                if (!empty($databases)) {
                    $config = array_pop($databases);
                }
            }

            try {
                $options = DataSourceOptions::fromArray([
                    ...$config,
                    'name' => $config['name'] ?? $dbName,
                    'database' => $config['database'] ?? $dbName,
                    'type' => DataSourceType::MSSQL,
                ]);
                $user = $options->username ?? 'sa';
                $password = $options->password ?? '';

                self::$connections[$type][$dbName] = new PDO(
                    dsn: self::buildMsSqlDsn($options->host, $options->port, $options->name),
                    username: $user,
                    password: $password
                );
                self::applyConnectionAttributes(self::$connections[$type][$dbName], SQLDialect::MSSQL);
            } catch (PDOException) {
                throw new DataSourceConnectionException(DataSourceType::MSSQL);
            }
        }

        return self::$connections[$type][$dbName];
    }

    /**
     * @param string $dbName
     * @return PDO
     * @throws DataSourceConnectionException
     */
    public static function getSQLiteConnection(string $dbName): PDO
    {
        $type = 'sqlite';
        self::ensureDriverIsAvailable(SQLDialect::SQLITE);

        if (empty($dbName)) {
            throw new DataSourceConnectionException();
        }

        $config = OrmRuntime::databaseConfigs()[$type][$dbName] ?? null;

        try {
            $path = self::isDirectSqlitePath($dbName)
                ? $dbName
                : ($config['path'] ?? null);

            if (empty($path)) {
                throw new DataSourceConnectionException(DataSourceType::SQLITE);
            }

            $path = SqlDialectHelper::normalizeSqlitePath((string)$path);
            $cacheKey = self::getSqliteCacheKey($dbName, $path);

            if (!isset(self::$connections[$type][$cacheKey]) || empty(self::$connections[$type][$cacheKey])) {
                self::$connections[$type][$cacheKey] = new PDO(dsn: "sqlite:$path");
                self::applyConnectionAttributes(self::$connections[$type][$cacheKey], SQLDialect::SQLITE);
            }

            return self::$connections[$type][$cacheKey];
        } catch (PDOException) {
            throw new DataSourceConnectionException(DataSourceType::SQLITE);
        }
    }

    private static function isDirectSqlitePath(string $path): bool
    {
        return $path === ':memory:'
            || str_starts_with($path, 'file:')
            || str_contains($path, DIRECTORY_SEPARATOR)
            || str_contains($path, '/')
            || preg_match('/\.(sqlite|sqlite3|db)$/i', $path) === 1;
    }

    private static function getSqliteCacheKey(string $dbName, ?string $path = null): string
    {
        $resolvedPath = $path
            ?? OrmRuntime::databaseConfigs()['sqlite'][$dbName]['path']
            ?? $dbName;

        if (!self::isDirectSqlitePath($resolvedPath)) {
            return $dbName;
        }

        return SqlDialectHelper::normalizeSqlitePath((string)$resolvedPath);
    }

    public static function retainSharedConnection(string $dbName, ?SQLDialect $dialect = SQLDialect::MYSQL): void
    {
        $type = self::getConnectionPoolType($dialect);
        $cacheKey = self::getConnectionCacheKey($dbName, $dialect);

        self::$sharedConnectionReferences[$type][$cacheKey] = (self::$sharedConnectionReferences[$type][$cacheKey] ?? 0) + 1;
    }

    private static function getConnectionPoolType(?SQLDialect $dialect): string
    {
        return match ($dialect) {
            SQLDialect::MARIADB => 'mysql',
            SQLDialect::MSSQL => 'mssql',
            SQLDialect::POSTGRESQL => 'pgsql',
            SQLDialect::SQLITE => 'sqlite',
            default => 'mysql',
        };
    }

    private static function getConnectionCacheKey(string $dbName, ?SQLDialect $dialect): string
    {
        return $dialect === SQLDialect::SQLITE
            ? self::getSqliteCacheKey($dbName)
            : $dbName;
    }

    public static function releaseSharedConnection(string $dbName, ?SQLDialect $dialect = SQLDialect::MYSQL): void
    {
        $type = self::getConnectionPoolType($dialect);
        $cacheKey = self::getConnectionCacheKey($dbName, $dialect);

        if (!isset(self::$sharedConnectionReferences[$type][$cacheKey])) {
            self::disconnectConnection($dbName, $dialect);
            return;
        }

        self::$sharedConnectionReferences[$type][$cacheKey]--;

        if (self::$sharedConnectionReferences[$type][$cacheKey] > 0) {
            return;
        }

        unset(self::$sharedConnectionReferences[$type][$cacheKey]);
        self::disconnectConnection($dbName, $dialect);
    }

    public static function disconnectConnection(string $dbName, ?SQLDialect $dialect = SQLDialect::MYSQL): void
    {
        $type = self::getConnectionPoolType($dialect);
        $cacheKey = self::getConnectionCacheKey($dbName, $dialect);
        $connection = self::$connections[$type][$cacheKey] ?? null;

        unset(self::$sharedConnectionReferences[$type][$cacheKey]);

        if ($connection instanceof PDO && $connection->inTransaction()) {
            $connection->rollBack();
        }

        self::$connections[$type][$cacheKey] = null;
        unset(self::$connections[$type][$cacheKey]);
    }

    public static function getRequiredPdoExtension(SQLDialect $dialect): ?string
    {
        return match ($dialect) {
            SQLDialect::MYSQL, SQLDialect::MARIADB => 'pdo_mysql',
            SQLDialect::POSTGRESQL => 'pdo_pgsql',
            SQLDialect::MSSQL => 'pdo_sqlsrv',
            SQLDialect::SQLITE => 'pdo_sqlite',
            default => null,
        };
    }

    public static function getRequiredPdoDriverName(SQLDialect $dialect): ?string
    {
        return match ($dialect) {
            SQLDialect::MYSQL, SQLDialect::MARIADB => 'mysql',
            SQLDialect::POSTGRESQL => 'pgsql',
            SQLDialect::MSSQL => 'sqlsrv',
            SQLDialect::SQLITE => 'sqlite',
            default => null,
        };
    }

    private static function ensureDriverIsAvailable(SQLDialect $dialect): void
    {
        $extension = self::getRequiredPdoExtension($dialect);
        $driver = self::getRequiredPdoDriverName($dialect);

        if ($extension === null || $driver === null) {
            return;
        }

        if (!extension_loaded($extension) || !in_array($driver, PDO::getAvailableDrivers(), true)) {
            $type = match ($dialect) {
            SQLDialect::MARIADB => DataSourceType::MARIADB,
            SQLDialect::MSSQL => DataSourceType::MSSQL,
            SQLDialect::POSTGRESQL => DataSourceType::POSTGRESQL,
            SQLDialect::SQLITE => DataSourceType::SQLITE,
            default => DataSourceType::MYSQL,
        };

            throw new DataSourceConnectionException(
                $type,
                sprintf('Missing required PDO driver. Install or enable the %s extension to use %s.', $extension, $type->value),
            );
        }
    }

    /**
     * @param string $dbName
     * @return PDO
     * @throws DataSourceConnectionException
     */
    public static function getMongoDbConnection(string $dbName): PDO
    {
        $type = 'mongodb';

        if (empty($dbName)) {
            throw new DataSourceConnectionException();
        }

        if (!isset(self::$connections[$type][$dbName]) || empty(self::$connections[$type][$dbName])) {
            self::validateDatabaseDetails(type: $type, dbName: $dbName);
            $config = OrmRuntime::databaseConfigs()[$type][$dbName];

            try {
                # TODO #16 Implement mongodb connection @amasiye
            } catch (PDOException) {
                die(new DataSourceConnectionException(DataSourceType::MONGODB));
            }
        }

        return self::$connections[$type][$dbName];
    }
}
