<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\MariaDb\MariaDbAlterDatabaseOption;
use Assegai\Orm\Queries\MariaDb\MariaDbAlterTableOption;
use Assegai\Orm\Queries\MariaDb\MariaDbDeleteFromStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbDescribeStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbTruncateStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbUseStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbQuery;
use Assegai\Orm\Queries\MariaDb\MariaDbRenameStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbRenameTableStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbSelectDefinition;
use Assegai\Orm\Queries\MariaDb\MariaDbSelectExpression;
use Assegai\Orm\Queries\MariaDb\MariaDbTableReference;
use Assegai\Orm\Queries\MySql\MySQLAlterDatabaseOption;
use Assegai\Orm\Queries\MySql\MySQLSelectExpression;
use Assegai\Orm\Queries\MySql\MySQLTableReference;
use Assegai\Orm\Queries\MySql\MySQLAlterTableOption;
use Assegai\Orm\Queries\MySql\MySQLDeleteFromStatement;
use Assegai\Orm\Queries\MySql\MySQLDescribeStatement;
use Assegai\Orm\Queries\MySql\MySQLTruncateStatement;
use Assegai\Orm\Queries\MySql\MySQLUseStatement;
use Assegai\Orm\Queries\MySql\MySQLInsertIntoStatement;
use Assegai\Orm\Queries\MySql\MySQLRenameStatement;
use Assegai\Orm\Queries\MySql\MySQLRenameTableStatement;
use Assegai\Orm\Queries\MySql\MySQLRenameDatabaseStatement;
use Assegai\Orm\Queries\MySql\MySQLSelectDefinition;
use Assegai\Orm\Queries\MySql\MySQLQuery;
use Assegai\Orm\Queries\MySql\MySQLUpdateDefinition;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLAlterTableOption;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLDeleteFromStatement;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLDescribeStatement;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLTruncateStatement;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLInsertIntoStatement;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLQuery;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLRenameStatement;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLRenameTableStatement;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLSelectDefinition;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLSelectExpression;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLTableReference;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLUpdateDefinition;
use Assegai\Orm\Queries\SQLite\SQLiteAlterTableOption;
use Assegai\Orm\Queries\SQLite\SQLiteDeleteFromStatement;
use Assegai\Orm\Queries\SQLite\SQLiteDescribeStatement;
use Assegai\Orm\Queries\SQLite\SQLiteTruncateStatement;
use Assegai\Orm\Queries\SQLite\SQLiteRenameStatement;
use Assegai\Orm\Queries\SQLite\SQLiteRenameTableStatement;
use Assegai\Orm\Queries\SQLite\SQLiteSelectDefinition;
use Assegai\Orm\Queries\SQLite\SQLiteSelectExpression;
use Assegai\Orm\Queries\SQLite\SQLiteTableReference;
use Assegai\Orm\Queries\Sql\ColumnType;
use Assegai\Orm\Queries\Sql\SQLColumnDefinition;
use Assegai\Orm\Queries\Sql\SQLDatabaseCreateDefinitionInterface;
use Assegai\Orm\Queries\Sql\SQLDatabaseDropDefinitionInterface;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLRenameDatabaseStatement;
use Assegai\Orm\Queries\Sql\SQLUseStatement;
use Assegai\Orm\Queries\Sql\SQLInsertIntoPriority;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class SqlDialectRenderingTest extends TestCase
{
    public function testPostgreSqlLimitClauseUsesOffsetSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL);

        $query
            ->select()
            ->all(['users.name'])
            ->from('users')
            ->limit(10, 20);

        self::assertSame('SELECT "users"."name" FROM "users" LIMIT 10 OFFSET 20', $query->queryString());
    }

    public function testMySqlLimitClauseKeepsOffsetCommaSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL);

        $query
            ->select()
            ->all(['users.name'])
            ->from('users')
            ->limit(10, 20);

        self::assertSame('SELECT `users`.`name` FROM `users` LIMIT 20,10', $query->queryString());
    }

    public function testSwitchToPostgresReturnsDedicatedPostgreSqlQueryBuilder(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL);
        $postgres = $query->switchToPostgres();

        $postgres
            ->select()
            ->all(['users.name'])
            ->from('users')
            ->limit(10, 20);

        self::assertInstanceOf(PostgreSQLQuery::class, $postgres);
        self::assertSame(SQLDialect::MYSQL, $query->getDialect());
        self::assertSame(SQLDialect::POSTGRESQL, $postgres->getDialect());
        self::assertSame('SELECT "users"."name" FROM "users" LIMIT 10 OFFSET 20', $postgres->queryString());
    }

    public function testSwitchToMysqlReturnsDedicatedMysqlQueryBuilder(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL);
        $mysql = $query->switchToMysql();

        $mysql
            ->select()
            ->all(['users.name'])
            ->from('users')
            ->limit(10, 20);

        self::assertInstanceOf(MySQLQuery::class, $mysql);
        self::assertSame(SQLDialect::POSTGRESQL, $query->getDialect());
        self::assertSame(SQLDialect::MYSQL, $mysql->getDialect());
        self::assertSame('SELECT `users`.`name` FROM `users` LIMIT 20,10', $mysql->queryString());
    }

    public function testDialectSpecificSelectBuildersExposeDialectOnlyFluentMethods(): void
    {
        $mysqlSelect = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->select();
        $postgresSelect = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->select();
        $sqliteSelect = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->select();
        $mariaDbSelect = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb()->select();

        self::assertInstanceOf(MySQLSelectDefinition::class, $mysqlSelect);
        self::assertTrue(method_exists($mysqlSelect, 'highPriority'));
        self::assertInstanceOf(PostgreSQLSelectDefinition::class, $postgresSelect);
        self::assertTrue(method_exists($postgresSelect, 'distinctOn'));
        self::assertInstanceOf(SQLiteSelectDefinition::class, $sqliteSelect);
        self::assertFalse(method_exists($sqliteSelect, 'highPriority'));
        self::assertInstanceOf(MariaDbSelectDefinition::class, $mariaDbSelect);
        self::assertTrue(method_exists($mariaDbSelect, 'highPriority'));
    }


    public function testDialectSpecificSelectChainsStayTypedAfterAllAndFrom(): void
    {
        $mysqlExpression = $this->createQuery(SQLDialect::POSTGRESQL)
            ->switchToMysql()
            ->select()
            ->all(['users.name']);
        $postgresExpression = $this->createQuery(SQLDialect::MYSQL)
            ->switchToPostgres()
            ->select()
            ->all(['users.name']);
        $sqliteExpression = $this->createQuery(SQLDialect::MYSQL)
            ->switchToSqlite()
            ->select()
            ->all(['users.name']);
        $mariaDbExpression = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMariaDb()
            ->select()
            ->all(['users.name']);

        self::assertInstanceOf(MySQLSelectExpression::class, $mysqlExpression);
        self::assertInstanceOf(PostgreSQLSelectExpression::class, $postgresExpression);
        self::assertInstanceOf(SQLiteSelectExpression::class, $sqliteExpression);
        self::assertInstanceOf(MariaDbSelectExpression::class, $mariaDbExpression);

        self::assertInstanceOf(MySQLTableReference::class, $mysqlExpression->from('users'));
        self::assertInstanceOf(PostgreSQLTableReference::class, $postgresExpression->from('users'));
        self::assertInstanceOf(SQLiteTableReference::class, $sqliteExpression->from('users'));
        self::assertInstanceOf(MariaDbTableReference::class, $mariaDbExpression->from('users'));
    }

    public function testMariaDbHighPrioritySelectChainStaysTyped(): void
    {
        $mariaDbSelect = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMariaDb()
            ->select()
            ->highPriority();

        self::assertInstanceOf(MariaDbSelectDefinition::class, $mariaDbSelect);

        $mariaDbExpression = $mariaDbSelect->all(['users.name']);
        self::assertInstanceOf(MariaDbSelectExpression::class, $mariaDbExpression);
        self::assertInstanceOf(MariaDbTableReference::class, $mariaDbExpression->from('users'));
    }

    public function testMySqlSelectHighPriorityCompilesOnlyOnMySqlBuilder(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql();

        $query
            ->select()
            ->highPriority()
            ->all(['users.name'])
            ->from('users');

        self::assertSame('SELECT HIGH_PRIORITY `users`.`name` FROM `users`', $query->queryString());
    }

    public function testPostgreSqlSelectDistinctOnCompilesOnlyOnPostgreSqlBuilder(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres();

        $query
            ->select()
            ->distinctOn(['users.id'])
            ->all(['users.id', 'users.name'])
            ->from('users');

        self::assertSame(
            'SELECT DISTINCT ON ("users"."id") "users"."id", "users"."name" FROM "users"',
            $query->queryString()
        );
    }
    public function testDialectSpecificDeleteBuildersExposeDialectOnlyFluentMethods(): void
    {
        $mysqlDelete = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->deleteFrom('users');
        $postgresDelete = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->deleteFrom('users');
        $sqliteDelete = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->deleteFrom('users');
        $mariaDbDelete = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb()->deleteFrom('users');

        self::assertInstanceOf(MySQLDeleteFromStatement::class, $mysqlDelete);
        self::assertTrue(method_exists($mysqlDelete, 'lowPriority'));
        self::assertTrue(method_exists($mysqlDelete, 'quick'));
        self::assertTrue(method_exists($mysqlDelete, 'ignore'));
        self::assertInstanceOf(PostgreSQLDeleteFromStatement::class, $postgresDelete);
        self::assertTrue(method_exists($postgresDelete, 'using'));
        self::assertTrue(method_exists($postgresDelete, 'returning'));
        self::assertInstanceOf(SQLiteDeleteFromStatement::class, $sqliteDelete);
        self::assertFalse(method_exists($sqliteDelete, 'using'));
        self::assertInstanceOf(MariaDbDeleteFromStatement::class, $mariaDbDelete);
        self::assertTrue(method_exists($mariaDbDelete, 'lowPriority'));
    }

    public function testMySqlDeleteBuilderCompilesMySqlOnlyModifiers(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql();

        $query
            ->deleteFrom('users')
            ->lowPriority()
            ->quick()
            ->ignore()
            ->where(['id' => 1]);

        self::assertSame('DELETE LOW_PRIORITY QUICK IGNORE FROM `users` WHERE `id`=?', $query->queryString());
    }

    public function testPostgreSqlDeleteBuilderCompilesUsingAndReturning(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres();

        $query
            ->deleteFrom('users', alias: 'u')
            ->using(['a' => 'accounts'])
            ->where('"u"."account_id" = "a"."id"')
            ->returning(['users.id', 'users.name']);

        self::assertSame(
            'DELETE FROM "users" AS "u" USING "accounts" AS "a" WHERE "u"."account_id" = "a"."id" RETURNING "users"."id", "users"."name"',
            $query->queryString()
        );
    }
    public function testDialectSpecificAlterBuildersExposeDialectOnlyFluentMethods(): void
    {
        $mysqlAlter = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->alter()->table('users');
        $postgresAlter = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->alter()->table('users');
        $sqliteAlter = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->alter()->table('users');
        $mariaDbAlter = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb()->alter()->table('users');

        self::assertInstanceOf(MySQLAlterTableOption::class, $mysqlAlter);
        self::assertTrue(method_exists($mysqlAlter, 'modifyColumn'));
        self::assertInstanceOf(PostgreSQLAlterTableOption::class, $postgresAlter);
        self::assertTrue(method_exists($postgresAlter, 'alterColumnType'));
        self::assertInstanceOf(SQLiteAlterTableOption::class, $sqliteAlter);
        self::assertFalse(method_exists($sqliteAlter, 'modifyColumn'));
        self::assertInstanceOf(MariaDbAlterTableOption::class, $mariaDbAlter);
        self::assertTrue(method_exists($mariaDbAlter, 'modifyColumn'));
    }


    public function testAlterDatabaseExistsOnlyOnMySqlFamilyBuilders(): void
    {
        $mysqlAlter = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->alter();
        $postgresAlter = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->alter();
        $sqliteAlter = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->alter();
        $mariaDbAlter = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb()->alter();

        self::assertTrue(method_exists($mysqlAlter, 'database'));
        self::assertFalse(method_exists($postgresAlter, 'database'));
        self::assertFalse(method_exists($sqliteAlter, 'database'));
        self::assertTrue(method_exists($mariaDbAlter, 'database'));
    }


    public function testMariaDbAlterDatabaseReturnsTypedMariaDbBuilder(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb();

        $statement = $query->alter()->database('app_db');

        self::assertInstanceOf(MariaDbAlterDatabaseOption::class, $statement);
        self::assertSame('ALTER DATABASE `app_db`', $query->queryString());
    }

    public function testMySqlAlterDatabaseCompilesMysqlFamilySyntax(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql();

        $statement = $query
            ->alter()
            ->database('app_db')
            ->setCharacterSet(SQLCharacterSet::UTF8MB4)
            ->setDefaultCollation('utf8mb4_unicode_ci');

        self::assertInstanceOf(MySQLAlterDatabaseOption::class, $statement);
        self::assertSame(
            'ALTER DATABASE `app_db` DEFAULT CHARACTER SET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci',
            $query->queryString()
        );
    }

    public function testMySqlAlterBuilderCompilesModifyColumnAndPositioning(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql();

        $query
            ->alter()
            ->table('users')
            ->addColumn(new SQLColumnDefinition('nickname', ColumnType::VARCHAR, 64, nullable: true, dialect: SQLDialect::MYSQL), afterColumn: 'name')
            ->modifyColumn(new SQLColumnDefinition('name', ColumnType::VARCHAR, 255, nullable: false, dialect: SQLDialect::MYSQL));

        self::assertSame(
            'ALTER TABLE `users` ADD COLUMN `nickname` VARCHAR(64) NULL AFTER `name` MODIFY COLUMN `name` VARCHAR(255) NOT NULL',
            $query->queryString()
        );
    }

    public function testPostgreSqlAlterBuilderCompilesTypedPostgreSqlAlterColumnSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres();

        $query
            ->alter()
            ->table('users')
            ->alterColumnType('name', 'VARCHAR(255)', '"name"::varchar')
            ->setDefault('name', 'guest');

        self::assertSame(
            'ALTER TABLE "users" ALTER COLUMN "name" TYPE VARCHAR(255) USING "name"::varchar ALTER COLUMN "name" SET DEFAULT \'guest\'',
            $query->queryString()
        );
    }
    public function testUseExistsOnlyOnDialectsThatSupportSwitchingDatabases(): void
    {
        $mysql = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql();
        $postgres = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres();
        $sqlite = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite();
        $mariaDb = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb();

        self::assertTrue(method_exists($mysql, 'use'));
        self::assertFalse(method_exists($postgres, 'use'));
        self::assertFalse(method_exists($sqlite, 'use'));
        self::assertTrue(method_exists($mariaDb, 'use'));
    }

    public function testDialectSpecificDescribeBuildersExposeTypedApiShapes(): void
    {
        $mysqlDescribe = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->describe('users');
        $postgresDescribe = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->describe('users');
        $sqliteDescribe = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->describe('users');
        $mariaDbDescribe = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb()->describe('users');

        self::assertInstanceOf(MySQLDescribeStatement::class, $mysqlDescribe);
        self::assertInstanceOf(PostgreSQLDescribeStatement::class, $postgresDescribe);
        self::assertInstanceOf(SQLiteDescribeStatement::class, $sqliteDescribe);
        self::assertInstanceOf(MariaDbDescribeStatement::class, $mariaDbDescribe);
    }

    public function testMysqlUseQuotesDatabaseIdentifier(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql();

        $statement = $query->use('app_db');

        self::assertInstanceOf(MySQLUseStatement::class, $statement);
        self::assertSame('USE `app_db`', $query->queryString());
    }

    public function testPostgreSqlDescribeUsesInformationSchemaQuery(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres();

        $statement = $query->describe('users');

        self::assertInstanceOf(PostgreSQLDescribeStatement::class, $statement);
        self::assertSame(
            'SELECT "column_name", "data_type", "is_nullable", "column_default" FROM "information_schema"."columns" WHERE "table_schema" = CURRENT_SCHEMA() AND "table_name" = ? ORDER BY "ordinal_position" ASC',
            $query->queryString()
        );
    }

    public function testSqliteDescribeUsesPragmaTableInfo(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite();

        $statement = $query->describe('users');

        self::assertInstanceOf(SQLiteDescribeStatement::class, $statement);
        self::assertSame('PRAGMA table_info("users")', $query->queryString());
    }

    public function testDialectSpecificTruncateBuildersExposeTypedApiShapes(): void
    {
        $mysqlTruncate = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->truncateTable('users');
        $postgresTruncate = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->truncateTable('users');
        $sqliteTruncate = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->truncateTable('users');
        $mariaDbTruncate = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb()->truncateTable('users');

        self::assertInstanceOf(MySQLTruncateStatement::class, $mysqlTruncate);
        self::assertInstanceOf(PostgreSQLTruncateStatement::class, $postgresTruncate);
        self::assertInstanceOf(SQLiteTruncateStatement::class, $sqliteTruncate);
        self::assertInstanceOf(MariaDbTruncateStatement::class, $mariaDbTruncate);
    }

    public function testMySqlTruncateUsesTruncateTableSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql();

        $statement = $query->truncateTable('users');

        self::assertInstanceOf(MySQLTruncateStatement::class, $statement);
        self::assertSame('TRUNCATE TABLE `users`', $query->queryString());
    }

    public function testPostgreSqlTruncateUsesQuotedTruncateSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres();

        $statement = $query->truncateTable('users');

        self::assertInstanceOf(PostgreSQLTruncateStatement::class, $statement);
        self::assertSame('TRUNCATE TABLE "users"', $query->queryString());
    }

    public function testSqliteTruncateUsesDeleteFromEquivalent(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite();

        $statement = $query->truncateTable('users');

        self::assertInstanceOf(SQLiteTruncateStatement::class, $statement);
        self::assertSame('DELETE FROM "users"', $query->queryString());
    }

    public function testDialectSpecificInsertBuildersExposeOnlySupportedFluentMethods(): void
    {
        $mysqlInsert = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMysql()
            ->insertInto('users')
            ->singleRow(['name']);
        $postgresInsert = $this->createQuery(SQLDialect::MYSQL)
            ->switchToPostgres()
            ->insertInto('users')
            ->singleRow(['name']);

        self::assertInstanceOf(MySQLInsertIntoStatement::class, $mysqlInsert);
        self::assertTrue(method_exists($mysqlInsert, 'onDuplicateKeyUpdate'));
        self::assertInstanceOf(PostgreSQLInsertIntoStatement::class, $postgresInsert);
        self::assertFalse(method_exists($postgresInsert, 'onDuplicateKeyUpdate'));
    }

    public function testDatabaseCapabilityInterfacesMatchDialectSupport(): void
    {
        $mysqlCreate = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->create();
        $postgresCreate = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->create();
        $sqliteCreate = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->create();

        $mysqlDrop = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->drop();
        $postgresDrop = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->drop();
        $sqliteDrop = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->drop();

        self::assertInstanceOf(SQLDatabaseCreateDefinitionInterface::class, $mysqlCreate);
        self::assertInstanceOf(SQLDatabaseCreateDefinitionInterface::class, $postgresCreate);
        self::assertNotInstanceOf(SQLDatabaseCreateDefinitionInterface::class, $sqliteCreate);

        self::assertInstanceOf(SQLDatabaseDropDefinitionInterface::class, $mysqlDrop);
        self::assertInstanceOf(SQLDatabaseDropDefinitionInterface::class, $postgresDrop);
        self::assertNotInstanceOf(SQLDatabaseDropDefinitionInterface::class, $sqliteDrop);
    }

    public function testDialectSpecificCreateBuildersExposeTypedApiShapes(): void
    {
        $mysqlCreate = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->create();
        $postgresCreate = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->create();
        $sqliteCreate = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->create();
        $mariaDbCreate = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb()->create();

        self::assertInstanceOf(\Assegai\Orm\Queries\MySql\MySQLCreateDefinition::class, $mysqlCreate);
        self::assertInstanceOf(\Assegai\Orm\Queries\MySql\MySQLCreateTableStatement::class, $mysqlCreate->table('users'));
        self::assertInstanceOf(\Assegai\Orm\Queries\MySql\MySQLCreateDatabaseStatement::class, $mysqlCreate->database('app_db'));

        self::assertInstanceOf(\Assegai\Orm\Queries\PostgreSql\PostgreSQLCreateDefinition::class, $postgresCreate);
        self::assertInstanceOf(\Assegai\Orm\Queries\PostgreSql\PostgreSQLCreateTableStatement::class, $postgresCreate->table('users'));
        self::assertInstanceOf(\Assegai\Orm\Queries\PostgreSql\PostgreSQLCreateDatabaseStatement::class, $postgresCreate->database('analytics'));

        self::assertInstanceOf(\Assegai\Orm\Queries\SQLite\SQLiteCreateDefinition::class, $sqliteCreate);
        self::assertInstanceOf(\Assegai\Orm\Queries\SQLite\SQLiteCreateTableStatement::class, $sqliteCreate->table('users'));
        self::assertFalse(method_exists($sqliteCreate, 'database'));

        self::assertInstanceOf(\Assegai\Orm\Queries\MariaDb\MariaDbCreateDefinition::class, $mariaDbCreate);
    }

    public function testMySqlCreateDatabaseUsesMySqlSpecificOptions(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql();

        $query
            ->create()
            ->database('app_db', defaultCharacterSet: 'utf8mb4', defaultCollation: 'utf8mb4_unicode_ci', defaultEncryption: true);

        self::assertSame(
            'CREATE DATABASE IF NOT EXISTS `app_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENCRYPTION \'Y\'',
            $query->queryString()
        );
    }

    public function testPostgreSqlCreateDatabaseUsesPostgreSqlSpecificOptions(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres();

        $query
            ->create()
            ->database('analytics', encoding: 'UTF8', owner: 'app_owner', template: 'template0');

        self::assertSame(
            'CREATE DATABASE "analytics" WITH ENCODING \'UTF8\' OWNER "app_owner" TEMPLATE "template0"',
            $query->queryString()
        );
    }

    public function testSqliteCreateTableUsesQuotedIdentifierWithoutDatabaseApi(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite();

        $query
            ->create()
            ->table('users');

        self::assertSame('CREATE TABLE IF NOT EXISTS "users"', $query->queryString());
    }

    public function testDialectSpecificDropBuildersExposeTypedApiShapes(): void
    {
        $mysqlDrop = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->drop();
        $postgresDrop = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->drop();
        $sqliteDrop = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->drop();
        $mariaDbDrop = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb()->drop();

        self::assertInstanceOf(\Assegai\Orm\Queries\MySql\MySQLDropDefinition::class, $mysqlDrop);
        self::assertInstanceOf(\Assegai\Orm\Queries\MySql\MySQLDropTableStatement::class, $mysqlDrop->table('users'));
        self::assertInstanceOf(\Assegai\Orm\Queries\MySql\MySQLDropDatabaseStatement::class, $mysqlDrop->database('app_db'));

        self::assertInstanceOf(\Assegai\Orm\Queries\PostgreSql\PostgreSQLDropDefinition::class, $postgresDrop);
        self::assertInstanceOf(\Assegai\Orm\Queries\PostgreSql\PostgreSQLDropTableStatement::class, $postgresDrop->table('users'));
        self::assertInstanceOf(\Assegai\Orm\Queries\PostgreSql\PostgreSQLDropDatabaseStatement::class, $postgresDrop->database('analytics'));

        self::assertInstanceOf(\Assegai\Orm\Queries\SQLite\SQLiteDropDefinition::class, $sqliteDrop);
        self::assertInstanceOf(\Assegai\Orm\Queries\SQLite\SQLiteDropTableStatement::class, $sqliteDrop->table('users'));
        self::assertFalse(method_exists($sqliteDrop, 'database'));

        self::assertInstanceOf(\Assegai\Orm\Queries\MariaDb\MariaDbDropDefinition::class, $mariaDbDrop);
    }

    public function testPostgreSqlDropDatabaseSupportsForce(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres();

        $query
            ->drop()
            ->database('analytics', checkIfExists: true, force: true);

        self::assertSame('DROP DATABASE IF EXISTS "analytics" WITH (FORCE)', $query->queryString());
    }

    public function testSqliteDropTableUsesQuotedIdentifier(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite();

        $query
            ->drop()
            ->table('users');

        self::assertSame('DROP TABLE IF EXISTS "users"', $query->queryString());
    }
    public function testDialectSpecificUpdateBuildersExposeOnlySupportedApiShapes(): void
    {
        $mysqlQuery = $this->createQuery(SQLDialect::MYSQL)->switchToMysql();
        $postgresQuery = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres();

        $mysqlUpdate = $mysqlQuery->update('users', lowPriority: true, ignore: true);
        $postgresUpdate = $postgresQuery->update('users');

        self::assertInstanceOf(MySQLUpdateDefinition::class, $mysqlUpdate);
        self::assertInstanceOf(PostgreSQLUpdateDefinition::class, $postgresUpdate);
        self::assertSame(3, (new ReflectionMethod(MySQLQuery::class, 'update'))->getNumberOfParameters());
        self::assertSame(1, (new ReflectionMethod(PostgreSQLQuery::class, 'update'))->getNumberOfParameters());
        self::assertSame('UPDATE LOW_PRIORITY IGNORE `users`', $mysqlQuery->queryString());
        self::assertSame('UPDATE "users"', $postgresQuery->queryString());
    }

    public function testSwitchToPostgreSqlAliasUsesPostgreSqlDialect(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToPostgreSql();

        self::assertSame(SQLDialect::POSTGRESQL, $query->getDialect());
    }

    public function testMysqlColumnDefinitionFallsBackToDefaultVarcharLengthWhenLengthIsEmpty(): void
    {
        $column = new SQLColumnDefinition('name', ColumnType::VARCHAR, '', nullable: false, dialect: SQLDialect::MYSQL);

        self::assertSame('`name` VARCHAR(255) NOT NULL', $column->queryString());
    }

    public function testFindWhereOptionsCompileUsesQueryDialect(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL);
        $where = new FindWhereOptions(conditions: ['status' => 'active']);

        self::assertSame('"status"=?', $where->compile($query));
    }

    public function testPostgreSqlUpdateBuilderQuotesIdentifiers(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL);

        $query
            ->update('users')
            ->set(['name' => 'Ada']);

        self::assertSame('UPDATE "users" SET "name"=?', $query->queryString());
    }

    public function testPostgreSqlMultipleInsertUsesPortableValuesSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL);

        $query
            ->insertInto('users')
            ->multipleRows(['name', 'email'])
            ->rows([
                ['Ada', 'ada@example.com'],
                ['Bob', 'bob@example.com'],
            ]);

        self::assertSame(
            'INSERT INTO "users" ("name", "email") VALUES (?, ?), (?, ?)',
            $query->queryString()
        );
    }

    public function testPostgreSqlSelectAndFromQuoteAliases(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL);

        $query
            ->select()
            ->all(['user_name' => 'users.name'])
            ->from(['u' => 'users']);

        self::assertSame(
            'SELECT "users"."name" AS "user_name" FROM "users" AS "u"',
            $query->queryString()
        );
    }


    public function testLegacySqlUseStatementWrapperRetainsMysqlFamilyBehavior(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL);

        $statement = new SQLUseStatement(query: $query, dbName: 'app_db');

        self::assertInstanceOf(SQLUseStatement::class, $statement);
        self::assertSame('USE `app_db`', $query->queryString());
    }

    public function testLegacySqlRenameDatabaseWrapperRetainsMysqlFamilyBehavior(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL);

        $statement = new SQLRenameDatabaseStatement(query: $query, oldDbName: 'legacy_db', newDbName: 'next_db');

        self::assertInstanceOf(SQLRenameDatabaseStatement::class, $statement);
        self::assertSame('CREATE DATABASE `next_db` / DROP DATABASE `legacy_db`', $query->queryString());
    }

    public function testMysqlFamilyPriorityCompatibilityWrapperRetainsConstants(): void
    {
        self::assertSame('LOW PRIORITY', SQLInsertIntoPriority::LOW_PRIORITY);
        self::assertSame('HIGH PRIORITY', SQLInsertIntoPriority::HIGH_PRIORITY);
    }

    public function testMysqlRenameDatabaseHelperUsesMysqlNamespace(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL);

        $statement = new MySQLRenameDatabaseStatement(query: $query, oldDbName: 'catalog', newDbName: 'catalog_v2');

        self::assertInstanceOf(MySQLRenameDatabaseStatement::class, $statement);
        self::assertSame('CREATE DATABASE `catalog_v2` / DROP DATABASE `catalog`', $query->queryString());
    }

    public function testDialectSpecificRenameBuildersExposeTypedTableStatements(): void
    {
        $mysqlRename = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->rename();
        $postgresRename = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->rename();
        $sqliteRename = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->rename();
        $mariaDbRename = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb()->rename();

        self::assertInstanceOf(MySQLRenameStatement::class, $mysqlRename);
        self::assertInstanceOf(MySQLRenameTableStatement::class, $mysqlRename->table('users', 'customers'));
        self::assertInstanceOf(PostgreSQLRenameStatement::class, $postgresRename);
        self::assertInstanceOf(PostgreSQLRenameTableStatement::class, $postgresRename->table('users', 'customers'));
        self::assertInstanceOf(SQLiteRenameStatement::class, $sqliteRename);
        self::assertInstanceOf(SQLiteRenameTableStatement::class, $sqliteRename->table('users', 'customers'));
        self::assertInstanceOf(MariaDbRenameStatement::class, $mariaDbRename);
        self::assertInstanceOf(MariaDbRenameTableStatement::class, $mariaDbRename->table('users', 'customers'));
    }

    public function testMySqlRenameTableUsesRenameTableSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql();

        $query->rename()->table('users', 'customers');

        self::assertSame('RENAME TABLE `users` TO `customers`', $query->queryString());
    }
    public function testPostgreSqlRenameTableUsesAlterTableSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL);

        $query->rename()->table('users', 'customers');

        self::assertSame('ALTER TABLE "users" RENAME TO "customers"', $query->queryString());
    }

    public function testPostgreSqlAlterBuilderQuotesIdentifiers(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL);

        $query->alter()->table('users');

        self::assertSame('ALTER TABLE "users"', $query->queryString());
    }

    public function testInsertReturningRowsAreAvailableToPostgreSqlQueries(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL);
        $query->getConnection()->exec('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL)');

        $query
            ->insertInto('users')
            ->singleRow(['name'])
            ->values(['Ada']);
        $query->appendQueryString('RETURNING "id", "name"');
        $executed = $query->execute();

        self::assertTrue($executed->isOK());
        self::assertSame([['id' => 1, 'name' => 'Ada']], $executed->getData());
        self::assertSame(1, $query->lastInsertId());
    }

    private function createQuery(SQLDialect $dialect): SQLQuery
    {
        return SQLQuery::forConnection(new PDO('sqlite::memory:'), dialect: $dialect);
    }
}
