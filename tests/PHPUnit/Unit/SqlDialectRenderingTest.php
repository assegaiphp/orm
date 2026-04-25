<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\MsSql\MsSqlAssignmentList;
use Assegai\Orm\Queries\MsSql\MsSqlColumnDefinition;
use Assegai\Orm\Queries\MsSql\MsSqlCreateDatabaseStatement;
use Assegai\Orm\Queries\MsSql\MsSqlCreateTableStatement;
use Assegai\Orm\Queries\MsSql\MsSqlDeleteFromStatement;
use Assegai\Orm\Queries\MsSql\MsSqlDescribeStatement;
use Assegai\Orm\Queries\MsSql\MsSqlDropDatabaseStatement;
use Assegai\Orm\Queries\MsSql\MsSqlDropTableStatement;
use Assegai\Orm\Queries\MsSql\MsSqlHavingClause;
use Assegai\Orm\Queries\MsSql\MsSqlInsertIntoMultipleStatement;
use Assegai\Orm\Queries\MsSql\MsSqlInsertIntoStatement;
use Assegai\Orm\Queries\MsSql\MsSqlJoinExpression;
use Assegai\Orm\Queries\MsSql\MsSqlJoinSpecification;
use Assegai\Orm\Queries\MsSql\MsSqlLimitClause;
use Assegai\Orm\Queries\MsSql\MsSqlQuery;
use Assegai\Orm\Queries\MsSql\MsSqlRenameStatement;
use Assegai\Orm\Queries\MsSql\MsSqlRenameTableStatement;
use Assegai\Orm\Queries\MsSql\MsSqlSelectDefinition;
use Assegai\Orm\Queries\MsSql\MsSqlSelectExpression;
use Assegai\Orm\Queries\MsSql\MsSqlTableOptions;
use Assegai\Orm\Queries\MsSql\MsSqlTableReference;
use Assegai\Orm\Queries\MsSql\MsSqlTruncateStatement;
use Assegai\Orm\Queries\MsSql\MsSqlUpdateDefinition;
use Assegai\Orm\Queries\MsSql\MsSqlUseStatement;
use Assegai\Orm\Queries\MsSql\MsSqlWhereClause;
use Assegai\Orm\Queries\MariaDb\MariaDbAlterDatabaseOption;
use Assegai\Orm\Queries\MariaDb\MariaDbAlterTableOption;
use Assegai\Orm\Queries\MariaDb\MariaDbAssignmentList;
use Assegai\Orm\Queries\MariaDb\MariaDbColumnDefinition;
use Assegai\Orm\Queries\MariaDb\MariaDbCreateDatabaseStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbCreateTableStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbTableOptions;
use Assegai\Orm\Queries\MariaDb\MariaDbDeleteFromStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbDescribeStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbDropDatabaseStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbDropTableStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbInsertIntoMultipleStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbInsertIntoStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbJoinExpression;
use Assegai\Orm\Queries\MariaDb\MariaDbJoinSpecification;
use Assegai\Orm\Queries\MariaDb\MariaDbLimitClause;
use Assegai\Orm\Queries\MariaDb\MariaDbTruncateStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbUseStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbQuery;
use Assegai\Orm\Queries\MariaDb\MariaDbRenameStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbRenameTableStatement;
use Assegai\Orm\Queries\MariaDb\MariaDbSelectDefinition;
use Assegai\Orm\Queries\MariaDb\MariaDbSelectExpression;
use Assegai\Orm\Queries\MariaDb\MariaDbTableReference;
use Assegai\Orm\Queries\MariaDb\MariaDbUpdateDefinition;
use Assegai\Orm\Queries\MariaDb\MariaDbWhereClause;
use Assegai\Orm\Queries\MariaDb\MariaDbHavingClause;
use Assegai\Orm\Queries\MySql\MySQLAlterDatabaseOption;
use Assegai\Orm\Queries\MySql\MySQLAssignmentList;
use Assegai\Orm\Queries\MySql\MySQLColumnDefinition;
use Assegai\Orm\Queries\MySql\MySQLCreateTableStatement;
use Assegai\Orm\Queries\MySql\MySQLTableOptions;
use Assegai\Orm\Queries\MySql\MySQLSelectExpression;
use Assegai\Orm\Queries\MySql\MySQLTableReference;
use Assegai\Orm\Queries\MySql\MySQLWhereClause;
use Assegai\Orm\Queries\MySql\MySQLHavingClause;
use Assegai\Orm\Queries\MySql\MySQLAlterTableOption;
use Assegai\Orm\Queries\MySql\MySQLDeleteFromStatement;
use Assegai\Orm\Queries\MySql\MySQLDescribeStatement;
use Assegai\Orm\Queries\MySql\MySQLJoinExpression;
use Assegai\Orm\Queries\MySql\MySQLJoinSpecification;
use Assegai\Orm\Queries\MySql\MySQLLimitClause;
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
use Assegai\Orm\Queries\PostgreSql\PostgreSQLAssignmentList;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLColumnDefinition;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLCreateTableStatement;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLJoinExpression;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLJoinSpecification;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLLimitClause;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLTableOptions;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLTruncateStatement;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLInsertIntoStatement;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLQuery;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLRenameStatement;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLRenameTableStatement;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLSelectDefinition;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLSelectExpression;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLTableReference;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLWhereClause;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLHavingClause;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLUpdateDefinition;
use Assegai\Orm\Queries\SQLite\SQLiteAlterTableOption;
use Assegai\Orm\Queries\SQLite\SQLiteDeleteFromStatement;
use Assegai\Orm\Queries\SQLite\SQLiteDescribeStatement;
use Assegai\Orm\Queries\SQLite\SQLiteAssignmentList;
use Assegai\Orm\Queries\SQLite\SQLiteColumnDefinition;
use Assegai\Orm\Queries\SQLite\SQLiteCreateTableStatement;
use Assegai\Orm\Queries\SQLite\SQLiteJoinExpression;
use Assegai\Orm\Queries\SQLite\SQLiteJoinSpecification;
use Assegai\Orm\Queries\SQLite\SQLiteLimitClause;
use Assegai\Orm\Queries\SQLite\SQLiteTableOptions;
use Assegai\Orm\Queries\SQLite\SQLiteTruncateStatement;
use Assegai\Orm\Queries\SQLite\SQLiteRenameStatement;
use Assegai\Orm\Queries\SQLite\SQLiteRenameTableStatement;
use Assegai\Orm\Queries\SQLite\SQLiteSelectDefinition;
use Assegai\Orm\Queries\SQLite\SQLiteSelectExpression;
use Assegai\Orm\Queries\SQLite\SQLiteTableReference;
use Assegai\Orm\Queries\SQLite\SQLiteWhereClause;
use Assegai\Orm\Queries\SQLite\SQLiteHavingClause;
use Assegai\Orm\Queries\Sql\ColumnType;
use Assegai\Orm\Queries\Sql\SQLColumnDefinition;
use Assegai\Orm\Queries\Sql\SQLDatabaseCreateDefinitionInterface;
use Assegai\Orm\Queries\Sql\SQLDatabaseDropDefinitionInterface;
use Assegai\Orm\Queries\Sql\SQLTableCreateDefinition;
use Assegai\Orm\Queries\Sql\SQLTableDropDefinition;
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

    public function testSwitchToMsSqlReturnsDedicatedMsSqlQueryBuilder(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL);
        $msSql = $query->switchToMsSql();

        $msSql
            ->select()
            ->top(5)
            ->all(['users.name'])
            ->from('users');

        self::assertInstanceOf(MsSqlQuery::class, $msSql);
        self::assertSame(SQLDialect::MYSQL, $query->getDialect());
        self::assertSame(SQLDialect::MSSQL, $msSql->getDialect());
        self::assertSame('SELECT TOP (5) [users].[name] FROM [users]', $msSql->queryString());
    }

    public function testDialectSpecificSelectBuildersExposeDialectOnlyFluentMethods(): void
    {
        $mysqlSelect = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->select();
        $postgresSelect = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->select();
        $sqliteSelect = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->select();
        $mariaDbSelect = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb()->select();
        $msSqlSelect = $this->createQuery(SQLDialect::MYSQL)->switchToMsSql()->select();

        self::assertInstanceOf(MySQLSelectDefinition::class, $mysqlSelect);
        self::assertTrue(method_exists($mysqlSelect, 'highPriority'));
        self::assertInstanceOf(PostgreSQLSelectDefinition::class, $postgresSelect);
        self::assertTrue(method_exists($postgresSelect, 'distinctOn'));
        self::assertInstanceOf(SQLiteSelectDefinition::class, $sqliteSelect);
        self::assertFalse(method_exists($sqliteSelect, 'highPriority'));
        self::assertInstanceOf(MariaDbSelectDefinition::class, $mariaDbSelect);
        self::assertTrue(method_exists($mariaDbSelect, 'highPriority'));
        self::assertInstanceOf(MsSqlSelectDefinition::class, $msSqlSelect);
        self::assertTrue(method_exists($msSqlSelect, 'top'));
        self::assertFalse(method_exists($msSqlSelect, 'highPriority'));
    }

    public function testDialectSpecificQueryRootsKeepTypedReturnSignaturesForSharedEntryPoints(): void
    {
        self::assertSame(\Assegai\Orm\Queries\MySql\MySQLAlterDefinition::class, (new ReflectionMethod(MySQLQuery::class, 'alter'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MySql\MySQLCreateDefinition::class, (new ReflectionMethod(MySQLQuery::class, 'create'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MySql\MySQLDropDefinition::class, (new ReflectionMethod(MySQLQuery::class, 'drop'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MySql\MySQLDescribeStatement::class, (new ReflectionMethod(MySQLQuery::class, 'describe'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MySql\MySQLInsertIntoDefinition::class, (new ReflectionMethod(MySQLQuery::class, 'insertInto'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MySql\MySQLSelectDefinition::class, (new ReflectionMethod(MySQLQuery::class, 'select'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MySql\MySQLDeleteFromStatement::class, (new ReflectionMethod(MySQLQuery::class, 'deleteFrom'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MySql\MySQLTruncateStatement::class, (new ReflectionMethod(MySQLQuery::class, 'truncateTable'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MySql\MySQLRenameStatement::class, (new ReflectionMethod(MySQLQuery::class, 'rename'))->getReturnType()?->getName());

        self::assertSame(\Assegai\Orm\Queries\PostgreSql\PostgreSQLAlterDefinition::class, (new ReflectionMethod(PostgreSQLQuery::class, 'alter'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\PostgreSql\PostgreSQLCreateDefinition::class, (new ReflectionMethod(PostgreSQLQuery::class, 'create'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\PostgreSql\PostgreSQLDropDefinition::class, (new ReflectionMethod(PostgreSQLQuery::class, 'drop'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\PostgreSql\PostgreSQLDescribeStatement::class, (new ReflectionMethod(PostgreSQLQuery::class, 'describe'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\PostgreSql\PostgreSQLInsertIntoDefinition::class, (new ReflectionMethod(PostgreSQLQuery::class, 'insertInto'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\PostgreSql\PostgreSQLSelectDefinition::class, (new ReflectionMethod(PostgreSQLQuery::class, 'select'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\PostgreSql\PostgreSQLDeleteFromStatement::class, (new ReflectionMethod(PostgreSQLQuery::class, 'deleteFrom'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\PostgreSql\PostgreSQLTruncateStatement::class, (new ReflectionMethod(PostgreSQLQuery::class, 'truncateTable'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\PostgreSql\PostgreSQLRenameStatement::class, (new ReflectionMethod(PostgreSQLQuery::class, 'rename'))->getReturnType()?->getName());

        self::assertSame(\Assegai\Orm\Queries\SQLite\SQLiteAlterDefinition::class, (new ReflectionMethod(\Assegai\Orm\Queries\SQLite\SQLiteQuery::class, 'alter'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\SQLite\SQLiteCreateDefinition::class, (new ReflectionMethod(\Assegai\Orm\Queries\SQLite\SQLiteQuery::class, 'create'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\SQLite\SQLiteDropDefinition::class, (new ReflectionMethod(\Assegai\Orm\Queries\SQLite\SQLiteQuery::class, 'drop'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\SQLite\SQLiteDescribeStatement::class, (new ReflectionMethod(\Assegai\Orm\Queries\SQLite\SQLiteQuery::class, 'describe'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\SQLite\SQLiteInsertIntoDefinition::class, (new ReflectionMethod(\Assegai\Orm\Queries\SQLite\SQLiteQuery::class, 'insertInto'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\SQLite\SQLiteSelectDefinition::class, (new ReflectionMethod(\Assegai\Orm\Queries\SQLite\SQLiteQuery::class, 'select'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\SQLite\SQLiteDeleteFromStatement::class, (new ReflectionMethod(\Assegai\Orm\Queries\SQLite\SQLiteQuery::class, 'deleteFrom'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\SQLite\SQLiteTruncateStatement::class, (new ReflectionMethod(\Assegai\Orm\Queries\SQLite\SQLiteQuery::class, 'truncateTable'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\SQLite\SQLiteRenameStatement::class, (new ReflectionMethod(\Assegai\Orm\Queries\SQLite\SQLiteQuery::class, 'rename'))->getReturnType()?->getName());

        self::assertSame(\Assegai\Orm\Queries\MariaDb\MariaDbAlterDefinition::class, (new ReflectionMethod(MariaDbQuery::class, 'alter'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MariaDb\MariaDbCreateDefinition::class, (new ReflectionMethod(MariaDbQuery::class, 'create'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MariaDb\MariaDbDropDefinition::class, (new ReflectionMethod(MariaDbQuery::class, 'drop'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MariaDb\MariaDbDescribeStatement::class, (new ReflectionMethod(MariaDbQuery::class, 'describe'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MariaDb\MariaDbInsertIntoDefinition::class, (new ReflectionMethod(MariaDbQuery::class, 'insertInto'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MariaDb\MariaDbSelectDefinition::class, (new ReflectionMethod(MariaDbQuery::class, 'select'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MariaDb\MariaDbDeleteFromStatement::class, (new ReflectionMethod(MariaDbQuery::class, 'deleteFrom'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MariaDb\MariaDbTruncateStatement::class, (new ReflectionMethod(MariaDbQuery::class, 'truncateTable'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MariaDb\MariaDbRenameStatement::class, (new ReflectionMethod(MariaDbQuery::class, 'rename'))->getReturnType()?->getName());

        self::assertSame(\Assegai\Orm\Queries\MsSql\MsSqlAlterDefinition::class, (new ReflectionMethod(MsSqlQuery::class, 'alter'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MsSql\MsSqlCreateDefinition::class, (new ReflectionMethod(MsSqlQuery::class, 'create'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MsSql\MsSqlDropDefinition::class, (new ReflectionMethod(MsSqlQuery::class, 'drop'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MsSql\MsSqlDescribeStatement::class, (new ReflectionMethod(MsSqlQuery::class, 'describe'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MsSql\MsSqlInsertIntoDefinition::class, (new ReflectionMethod(MsSqlQuery::class, 'insertInto'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MsSql\MsSqlSelectDefinition::class, (new ReflectionMethod(MsSqlQuery::class, 'select'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MsSql\MsSqlDeleteFromStatement::class, (new ReflectionMethod(MsSqlQuery::class, 'deleteFrom'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MsSql\MsSqlTruncateStatement::class, (new ReflectionMethod(MsSqlQuery::class, 'truncateTable'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MsSql\MsSqlRenameStatement::class, (new ReflectionMethod(MsSqlQuery::class, 'rename'))->getReturnType()?->getName());
        self::assertSame(\Assegai\Orm\Queries\MsSql\MsSqlUseStatement::class, (new ReflectionMethod(MsSqlQuery::class, 'use'))->getReturnType()?->getName());
    }


    public function testDialectSpecificSelectAggregateMethodsKeepTypedReturnSignatures(): void
    {
        foreach ([
            [MySQLSelectDefinition::class, MySQLSelectExpression::class],
            [PostgreSQLSelectDefinition::class, PostgreSQLSelectExpression::class],
            [SQLiteSelectDefinition::class, SQLiteSelectExpression::class],
            [MariaDbSelectDefinition::class, MariaDbSelectExpression::class],
            [MsSqlSelectDefinition::class, MsSqlSelectExpression::class],
        ] as [$definitionClass, $expressionClass]) {
            foreach (['all', 'count', 'avg', 'sum'] as $methodName) {
                $returnType = (new ReflectionMethod($definitionClass, $methodName))->getReturnType();

                self::assertNotNull($returnType);
                self::assertSame($expressionClass, $returnType->getName());
            }
        }
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
        $msSqlExpression = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMsSql()
            ->select()
            ->all(['users.name']);

        self::assertInstanceOf(MySQLSelectExpression::class, $mysqlExpression);
        self::assertInstanceOf(PostgreSQLSelectExpression::class, $postgresExpression);
        self::assertInstanceOf(SQLiteSelectExpression::class, $sqliteExpression);
        self::assertInstanceOf(MariaDbSelectExpression::class, $mariaDbExpression);
        self::assertInstanceOf(MsSqlSelectExpression::class, $msSqlExpression);

        self::assertInstanceOf(MySQLTableReference::class, $mysqlExpression->from('users'));
        self::assertInstanceOf(PostgreSQLTableReference::class, $postgresExpression->from('users'));
        self::assertInstanceOf(SQLiteTableReference::class, $sqliteExpression->from('users'));
        self::assertInstanceOf(MariaDbTableReference::class, $mariaDbExpression->from('users'));
        self::assertInstanceOf(MsSqlTableReference::class, $msSqlExpression->from('users'));
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

    public function testDialectSpecificTableReferenceChainsStayTypedAfterWhereAndHaving(): void
    {
        $mysqlTableReference = $this->createQuery(SQLDialect::POSTGRESQL)
            ->switchToMysql()
            ->select()
            ->all(['users.name'])
            ->from('users');
        $postgresTableReference = $this->createQuery(SQLDialect::MYSQL)
            ->switchToPostgres()
            ->select()
            ->all(['users.name'])
            ->from('users');
        $sqliteTableReference = $this->createQuery(SQLDialect::MYSQL)
            ->switchToSqlite()
            ->select()
            ->all(['users.name'])
            ->from('users');
        $mariaDbTableReference = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMariaDb()
            ->select()
            ->all(['users.name'])
            ->from('users');
        $msSqlTableReference = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMsSql()
            ->select()
            ->all(['users.name'])
            ->from('users');

        self::assertInstanceOf(MySQLWhereClause::class, $mysqlTableReference->where('`users`.`active` = 1'));
        self::assertInstanceOf(PostgreSQLWhereClause::class, $postgresTableReference->where('"users"."active" = true'));
        self::assertInstanceOf(SQLiteWhereClause::class, $sqliteTableReference->where('"users"."active" = 1'));
        self::assertInstanceOf(MariaDbWhereClause::class, $mariaDbTableReference->where('`users`.`active` = 1'));
        self::assertInstanceOf(MsSqlWhereClause::class, $msSqlTableReference->where('[users].[active] = 1'));

        self::assertInstanceOf(MySQLHavingClause::class, $mysqlTableReference->having('COUNT(*) > 1'));
        self::assertInstanceOf(PostgreSQLHavingClause::class, $postgresTableReference->having('COUNT(*) > 1'));
        self::assertInstanceOf(SQLiteHavingClause::class, $sqliteTableReference->having('COUNT(*) > 1'));
        self::assertInstanceOf(MariaDbHavingClause::class, $mariaDbTableReference->having('COUNT(*) > 1'));
        self::assertInstanceOf(MsSqlHavingClause::class, $msSqlTableReference->having('COUNT(*) > 1'));
    }

    public function testDialectSpecificJoinChainsStayTypedAfterJoinOnWhereAndNestedJoin(): void
    {
        $mysqlTableReference = $this->createQuery(SQLDialect::POSTGRESQL)
            ->switchToMysql()
            ->select()
            ->all(['users.name'])
            ->from('users');
        $postgresTableReference = $this->createQuery(SQLDialect::MYSQL)
            ->switchToPostgres()
            ->select()
            ->all(['users.name'])
            ->from('users');
        $sqliteTableReference = $this->createQuery(SQLDialect::MYSQL)
            ->switchToSqlite()
            ->select()
            ->all(['users.name'])
            ->from('users');
        $mariaDbTableReference = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMariaDb()
            ->select()
            ->all(['users.name'])
            ->from('users');
        $msSqlTableReference = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMsSql()
            ->select()
            ->all(['users.name'])
            ->from('users');

        $mysqlJoinExpression = $mysqlTableReference->join('profiles');
        $postgresJoinExpression = $postgresTableReference->join('profiles');
        $sqliteJoinExpression = $sqliteTableReference->join('profiles');
        $mariaDbJoinExpression = $mariaDbTableReference->join('profiles');
        $msSqlJoinExpression = $msSqlTableReference->join('profiles');

        self::assertInstanceOf(MySQLJoinExpression::class, $mysqlJoinExpression);
        self::assertInstanceOf(PostgreSQLJoinExpression::class, $postgresJoinExpression);
        self::assertInstanceOf(SQLiteJoinExpression::class, $sqliteJoinExpression);
        self::assertInstanceOf(MariaDbJoinExpression::class, $mariaDbJoinExpression);
        self::assertInstanceOf(MsSqlJoinExpression::class, $msSqlJoinExpression);

        $mysqlJoinSpecification = $mysqlJoinExpression->on('`users`.`profile_id` = `profiles`.`id`');
        $postgresJoinSpecification = $postgresJoinExpression->on('"users"."profile_id" = "profiles"."id"');
        $sqliteJoinSpecification = $sqliteJoinExpression->on('"users"."profile_id" = "profiles"."id"');
        $mariaDbJoinSpecification = $mariaDbJoinExpression->on('`users`.`profile_id` = `profiles`.`id`');
        $msSqlJoinSpecification = $msSqlJoinExpression->on('[users].[profile_id] = [profiles].[id]');

        self::assertInstanceOf(MySQLJoinSpecification::class, $mysqlJoinSpecification);
        self::assertInstanceOf(PostgreSQLJoinSpecification::class, $postgresJoinSpecification);
        self::assertInstanceOf(SQLiteJoinSpecification::class, $sqliteJoinSpecification);
        self::assertInstanceOf(MariaDbJoinSpecification::class, $mariaDbJoinSpecification);
        self::assertInstanceOf(MsSqlJoinSpecification::class, $msSqlJoinSpecification);

        self::assertInstanceOf(MySQLWhereClause::class, $mysqlJoinSpecification->where('`profiles`.`active` = 1'));
        self::assertInstanceOf(PostgreSQLWhereClause::class, $postgresJoinSpecification->where('"profiles"."active" = true'));
        self::assertInstanceOf(SQLiteWhereClause::class, $sqliteJoinSpecification->where('"profiles"."active" = 1'));
        self::assertInstanceOf(MariaDbWhereClause::class, $mariaDbJoinSpecification->where('`profiles`.`active` = 1'));
        self::assertInstanceOf(MsSqlWhereClause::class, $msSqlJoinSpecification->where('[profiles].[active] = 1'));

        self::assertInstanceOf(MySQLJoinExpression::class, $mysqlJoinSpecification->leftJoin('teams'));
        self::assertInstanceOf(PostgreSQLJoinExpression::class, $postgresJoinSpecification->leftJoin('teams'));
        self::assertInstanceOf(SQLiteJoinExpression::class, $sqliteJoinSpecification->leftJoin('teams'));
        self::assertInstanceOf(MariaDbJoinExpression::class, $mariaDbJoinSpecification->leftJoin('teams'));
        self::assertInstanceOf(MsSqlJoinExpression::class, $msSqlJoinSpecification->leftJoin('teams'));
    }

    public function testDialectSpecificLimitBuildersStayTypedAfterFromAndWhere(): void
    {
        $mysqlTableReference = $this->createQuery(SQLDialect::POSTGRESQL)
            ->switchToMysql()
            ->select()
            ->all(['users.name'])
            ->from('users');
        $postgresTableReference = $this->createQuery(SQLDialect::MYSQL)
            ->switchToPostgres()
            ->select()
            ->all(['users.name'])
            ->from('users');
        $sqliteTableReference = $this->createQuery(SQLDialect::MYSQL)
            ->switchToSqlite()
            ->select()
            ->all(['users.name'])
            ->from('users');
        $mariaDbTableReference = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMariaDb()
            ->select()
            ->all(['users.name'])
            ->from('users');
        $msSqlTableReference = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMsSql()
            ->select()
            ->all(['users.name'])
            ->from('users');

        self::assertInstanceOf(MySQLLimitClause::class, $mysqlTableReference->limit(10, 20));
        self::assertInstanceOf(PostgreSQLLimitClause::class, $postgresTableReference->limit(10, 20));
        self::assertInstanceOf(SQLiteLimitClause::class, $sqliteTableReference->limit(10, 20));
        self::assertInstanceOf(MariaDbLimitClause::class, $mariaDbTableReference->limit(10, 20));
        self::assertInstanceOf(MsSqlLimitClause::class, $msSqlTableReference->limit(10, 20));

        $mysqlWhereClause = $this->createQuery(SQLDialect::POSTGRESQL)
            ->switchToMysql()
            ->select()
            ->all(['users.name'])
            ->from('users')
            ->where('`users`.`active` = 1');
        $postgresWhereClause = $this->createQuery(SQLDialect::MYSQL)
            ->switchToPostgres()
            ->select()
            ->all(['users.name'])
            ->from('users')
            ->where('"users"."active" = true');
        $sqliteWhereClause = $this->createQuery(SQLDialect::MYSQL)
            ->switchToSqlite()
            ->select()
            ->all(['users.name'])
            ->from('users')
            ->where('"users"."active" = 1');
        $mariaDbWhereClause = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMariaDb()
            ->select()
            ->all(['users.name'])
            ->from('users')
            ->where('`users`.`active` = 1');
        $msSqlWhereClause = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMsSql()
            ->select()
            ->all(['users.name'])
            ->from('users')
            ->where('[users].[active] = 1');

        self::assertInstanceOf(MySQLLimitClause::class, $mysqlWhereClause->limit(5));
        self::assertInstanceOf(PostgreSQLLimitClause::class, $postgresWhereClause->limit(5));
        self::assertInstanceOf(SQLiteLimitClause::class, $sqliteWhereClause->limit(5));
        self::assertInstanceOf(MariaDbLimitClause::class, $mariaDbWhereClause->limit(5));
        self::assertInstanceOf(MsSqlLimitClause::class, $msSqlWhereClause->limit(5));
    }

    public function testMsSqlSelectTopClauseUsesSqlServerSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::MSSQL);

        $query
            ->select()
            ->top(3)
            ->all(['users.name'])
            ->from('users');

        self::assertSame('SELECT TOP (3) [users].[name] FROM [users]', $query->queryString());
    }

    public function testMsSqlLimitClauseUsesOffsetFetchSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::MSSQL);

        $query
            ->select()
            ->all(['users.name'])
            ->from('users')
            ->limit(10, 20);

        self::assertSame(
            'SELECT [users].[name] FROM [users] ORDER BY (SELECT 0) OFFSET 20 ROWS FETCH NEXT 10 ROWS ONLY',
            $query->queryString()
        );
    }

    public function testMsSqlLimitClauseKeepsExistingOrderByBeforeOffsetFetch(): void
    {
        $query = $this->createQuery(SQLDialect::MSSQL);

        $query
            ->select()
            ->all(['users.name'])
            ->from('users')
            ->orderBy(['users.name' => 'ASC'])
            ->limit(10, 20);

        self::assertSame(
            'SELECT [users].[name] FROM [users] ORDER BY [users].[name] ASC OFFSET 20 ROWS FETCH NEXT 10 ROWS ONLY',
            $query->queryString()
        );
    }

    public function testMsSqlUseStatementRendersSqlServerSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::MSSQL);

        $use = $query->use('analytics');

        self::assertInstanceOf(MsSqlUseStatement::class, $use);
        self::assertSame('USE [analytics]', $query->queryString());
    }

    public function testMsSqlDescribeUsesInformationSchemaQuery(): void
    {
        $query = $this->createQuery(SQLDialect::MSSQL);

        $describe = $query->describe('users');

        self::assertInstanceOf(MsSqlDescribeStatement::class, $describe);
        self::assertSame(
            'SELECT [COLUMN_NAME], [DATA_TYPE], [IS_NULLABLE], [COLUMN_DEFAULT] FROM [INFORMATION_SCHEMA].[COLUMNS] WHERE [TABLE_NAME] = ? ORDER BY [ORDINAL_POSITION] ASC',
            $query->queryString()
        );
    }

    public function testMsSqlRenameTableUsesSpRenameSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::MSSQL);

        $rename = $query->rename()->table('users', 'archived_users');

        self::assertInstanceOf(MsSqlRenameTableStatement::class, $rename);
        self::assertSame("EXEC sp_rename N'[users]', N'archived_users', N'OBJECT'", $query->queryString());
    }

    public function testMsSqlCreateAndDropDatabaseRenderSqlServerSyntax(): void
    {
        $createQuery = $this->createQuery(SQLDialect::MSSQL);
        $create = $createQuery->create()->database('analytics');

        self::assertInstanceOf(MsSqlCreateDatabaseStatement::class, $create);
        self::assertSame("IF DB_ID(N'analytics') IS NULL CREATE DATABASE [analytics]", $createQuery->queryString());

        $dropQuery = $this->createQuery(SQLDialect::MSSQL);
        $drop = $dropQuery->drop()->database('analytics');

        self::assertInstanceOf(MsSqlDropDatabaseStatement::class, $drop);
        self::assertSame('DROP DATABASE [analytics]', $dropQuery->queryString());
    }

    public function testMsSqlAlterBuilderUsesSqlServerSpecificAddColumnSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::MSSQL);

        $query
            ->alter()
            ->table('users')
            ->addColumn(new SQLColumnDefinition('nickname', ColumnType::VARCHAR, 64, nullable: true, dialect: SQLDialect::MSSQL));

        self::assertSame('ALTER TABLE [users] ADD [nickname] VARCHAR(64) NULL', $query->queryString());
    }

    public function testMsSqlAlterBuilderUsesSpRenameForColumns(): void
    {
        $query = $this->createQuery(SQLDialect::MSSQL);

        $query
            ->alter()
            ->table('reporting.users')
            ->renameColumn('nickname', 'display_name');

        self::assertSame(
            "EXEC sp_rename N'[reporting].[users].[nickname]', N'display_name', N'COLUMN'",
            $query->queryString()
        );
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

    public function testDialectSpecificAlterEntryPointsKeepTypedReturnSignatures(): void
    {
        self::assertSame(
            MySQLAlterTableOption::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\MySql\MySQLAlterDefinition::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            PostgreSQLAlterTableOption::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\PostgreSql\PostgreSQLAlterDefinition::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            SQLiteAlterTableOption::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\SQLite\SQLiteAlterDefinition::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            MariaDbAlterTableOption::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\MariaDb\MariaDbAlterDefinition::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            MySQLAlterDatabaseOption::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\MySql\MySQLAlterDefinition::class, 'database'))->getReturnType()?->getName()
        );
        self::assertSame(
            MariaDbAlterDatabaseOption::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\MariaDb\MariaDbAlterDefinition::class, 'database'))->getReturnType()?->getName()
        );
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

    public function testMariaDbUseQuotesDatabaseIdentifier(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb();

        $statement = $query->use('app_db');

        self::assertInstanceOf(MariaDbUseStatement::class, $statement);
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

    public function testMySqlDescribeUsesQuotedDescribeSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql();

        $statement = $query->describe('users');

        self::assertInstanceOf(MySQLDescribeStatement::class, $statement);
        self::assertSame('DESCRIBE `users`', $query->queryString());
    }

    public function testMariaDbDescribeUsesQuotedDescribeSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb();

        $statement = $query->describe('users');

        self::assertInstanceOf(MariaDbDescribeStatement::class, $statement);
        self::assertSame('DESCRIBE `users`', $query->queryString());
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

    public function testMariaDbTruncateUsesQuotedTruncateSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb();

        $statement = $query->truncateTable('users');

        self::assertInstanceOf(MariaDbTruncateStatement::class, $statement);
        self::assertSame('TRUNCATE TABLE `users`', $query->queryString());
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
        $mariaDbInsert = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMariaDb()
            ->insertInto('users')
            ->singleRow(['name']);
        $postgresInsert = $this->createQuery(SQLDialect::MYSQL)
            ->switchToPostgres()
            ->insertInto('users')
            ->singleRow(['name']);

        self::assertInstanceOf(MySQLInsertIntoStatement::class, $mysqlInsert);
        self::assertTrue(method_exists($mysqlInsert, 'onDuplicateKeyUpdate'));
        self::assertFalse(method_exists($mysqlInsert, 'returning'));
        self::assertInstanceOf(MariaDbInsertIntoStatement::class, $mariaDbInsert);
        self::assertTrue(method_exists($mariaDbInsert, 'onDuplicateKeyUpdate'));
        self::assertFalse(method_exists($mariaDbInsert, 'returning'));
        self::assertInstanceOf(PostgreSQLInsertIntoStatement::class, $postgresInsert);
        self::assertFalse(method_exists($postgresInsert, 'onDuplicateKeyUpdate'));
        self::assertTrue(method_exists($postgresInsert, 'returning'));
    }

    public function testMySqlSingleRowInsertAppendsColumnsAndValues(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql();

        $query
            ->insertInto('users')
            ->singleRow(['name', 'email'])
            ->values(['Ada', 'ada@example.com']);

        self::assertSame(
            'INSERT INTO `users` (`name`, `email`) VALUES(?, ?) ',
            $query->queryString()
        );
    }

    public function testMariaDbSingleRowInsertAppendsColumnsAndValues(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb();

        $query
            ->insertInto('users')
            ->singleRow(['name', 'email'])
            ->values(['Ada', 'ada@example.com']);

        self::assertSame(
            'INSERT INTO `users` (`name`, `email`) VALUES(?, ?) ',
            $query->queryString()
        );
    }

    public function testDatabaseCapabilityInterfacesMatchDialectSupport(): void
    {
        $mysqlCreate = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->create();
        $postgresCreate = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->create();
        $sqliteCreate = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->create();

        $mysqlDrop = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->drop();
        $postgresDrop = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->drop();
        $sqliteDrop = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->drop();

        self::assertInstanceOf(SQLTableCreateDefinition::class, $mysqlCreate);
        self::assertInstanceOf(SQLTableCreateDefinition::class, $postgresCreate);
        self::assertInstanceOf(SQLTableCreateDefinition::class, $sqliteCreate);

        self::assertInstanceOf(SQLDatabaseCreateDefinitionInterface::class, $mysqlCreate);
        self::assertInstanceOf(SQLDatabaseCreateDefinitionInterface::class, $postgresCreate);
        self::assertNotInstanceOf(SQLDatabaseCreateDefinitionInterface::class, $sqliteCreate);

        self::assertInstanceOf(SQLTableDropDefinition::class, $mysqlDrop);
        self::assertInstanceOf(SQLTableDropDefinition::class, $postgresDrop);
        self::assertInstanceOf(SQLTableDropDefinition::class, $sqliteDrop);

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
        self::assertInstanceOf(MariaDbCreateTableStatement::class, $mariaDbCreate->table('users'));
        self::assertInstanceOf(MariaDbCreateDatabaseStatement::class, $mariaDbCreate->database('app_db'));
    }

    public function testDialectSpecificCreateEntryPointsKeepTypedReturnSignatures(): void
    {
        self::assertSame(
            \Assegai\Orm\Queries\MySql\MySQLCreateTableStatement::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\MySql\MySQLCreateDefinition::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            \Assegai\Orm\Queries\MySql\MySQLCreateDatabaseStatement::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\MySql\MySQLCreateDefinition::class, 'database'))->getReturnType()?->getName()
        );
        self::assertSame(
            \Assegai\Orm\Queries\PostgreSql\PostgreSQLCreateTableStatement::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\PostgreSql\PostgreSQLCreateDefinition::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            \Assegai\Orm\Queries\PostgreSql\PostgreSQLCreateDatabaseStatement::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\PostgreSql\PostgreSQLCreateDefinition::class, 'database'))->getReturnType()?->getName()
        );
        self::assertSame(
            \Assegai\Orm\Queries\SQLite\SQLiteCreateTableStatement::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\SQLite\SQLiteCreateDefinition::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            MariaDbCreateTableStatement::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\MariaDb\MariaDbCreateDefinition::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            MariaDbCreateDatabaseStatement::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\MariaDb\MariaDbCreateDefinition::class, 'database'))->getReturnType()?->getName()
        );
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

    public function testMariaDbCreateTableUsesQuotedIdentifier(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb();

        $query
            ->create()
            ->table('users');

        self::assertSame('CREATE TABLE IF NOT EXISTS `users`', $query->queryString());
    }

    public function testMsSqlCreateTableUsesSqlServerGuardSyntax(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToMsSql();

        $query
            ->create()
            ->table('users');

        self::assertSame("IF OBJECT_ID(N'[users]', N'U') IS NULL CREATE TABLE [users]", $query->queryString());
    }

    public function testDialectSpecificCreateTableColumnsBuildersStayTyped(): void
    {
        $mysqlTable = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql()->create()->table('users');
        $postgresTable = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres()->create()->table('users');
        $sqliteTable = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite()->create()->table('users');
        $mariaDbTable = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb()->create()->table('users');
        $msSqlTable = $this->createQuery(SQLDialect::MYSQL)->switchToMsSql()->create()->table('users');

        self::assertInstanceOf(MySQLCreateTableStatement::class, $mysqlTable);
        self::assertInstanceOf(PostgreSQLCreateTableStatement::class, $postgresTable);
        self::assertInstanceOf(SQLiteCreateTableStatement::class, $sqliteTable);
        self::assertInstanceOf(MariaDbCreateTableStatement::class, $mariaDbTable);
        self::assertInstanceOf(MsSqlCreateTableStatement::class, $msSqlTable);

        self::assertInstanceOf(
            MySQLTableOptions::class,
            $mysqlTable->columns([new MySQLColumnDefinition('id', ColumnType::INT, nullable: false)])
        );
        self::assertInstanceOf(
            PostgreSQLTableOptions::class,
            $postgresTable->columns([new PostgreSQLColumnDefinition('id', ColumnType::INT, nullable: false)])
        );
        self::assertInstanceOf(
            SQLiteTableOptions::class,
            $sqliteTable->columns([new SQLiteColumnDefinition('id', ColumnType::INT, nullable: false)])
        );
        self::assertInstanceOf(
            MariaDbTableOptions::class,
            $mariaDbTable->columns([new MariaDbColumnDefinition('id', ColumnType::INT, nullable: false)])
        );
        self::assertInstanceOf(
            MsSqlTableOptions::class,
            $msSqlTable->columns([new MsSqlColumnDefinition('id', ColumnType::INT, nullable: false)])
        );
    }

    public function testMySqlCreateTableColumnsAppendRenderedBody(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql();

        $query
            ->create()
            ->table('users')
            ->columns([
                '`id` INT NOT NULL PRIMARY KEY',
                '`name` VARCHAR(255) NOT NULL',
            ]);

        self::assertSame(
            'CREATE TABLE IF NOT EXISTS `users` (`id` INT NOT NULL PRIMARY KEY, `name` VARCHAR(255) NOT NULL)',
            $query->queryString()
        );
    }

    public function testPostgreSqlCreateTableColumnsAppendRenderedBody(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres();

        $query
            ->create()
            ->table('users')
            ->columns([
                '"id" INTEGER NOT NULL PRIMARY KEY',
                '"name" VARCHAR(255) NOT NULL',
            ]);

        self::assertSame(
            'CREATE TABLE IF NOT EXISTS "users" ("id" INTEGER NOT NULL PRIMARY KEY, "name" VARCHAR(255) NOT NULL)',
            $query->queryString()
        );
    }

    public function testMsSqlCreateTableColumnsAppendRenderedBody(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToMsSql();

        $query
            ->create()
            ->table('users')
            ->columns([
                '[id] INT NOT NULL PRIMARY KEY',
                '[name] NVARCHAR(255) NOT NULL',
            ]);

        self::assertSame(
            "IF OBJECT_ID(N'[users]', N'U') IS NULL CREATE TABLE [users] ([id] INT NOT NULL PRIMARY KEY, [name] NVARCHAR(255) NOT NULL)",
            $query->queryString()
        );
    }

    public function testSharedCreateTableBodySuppressesDuplicatePrimaryKeyClauses(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql();

        $query
            ->create()
            ->table('users')
            ->columns([
                '`id` INT NOT NULL PRIMARY KEY',
                '`legacy_id` INT PRIMARY KEY',
            ]);

        self::assertSame(
            'CREATE TABLE IF NOT EXISTS `users` (`id` INT NOT NULL PRIMARY KEY, `legacy_id` INT)',
            $query->queryString()
        );
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
        self::assertInstanceOf(MariaDbDropTableStatement::class, $mariaDbDrop->table('users'));
        self::assertInstanceOf(MariaDbDropDatabaseStatement::class, $mariaDbDrop->database('app_db'));
    }

    public function testDialectSpecificDropEntryPointsKeepTypedReturnSignatures(): void
    {
        self::assertSame(
            \Assegai\Orm\Queries\MySql\MySQLDropTableStatement::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\MySql\MySQLDropDefinition::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            \Assegai\Orm\Queries\MySql\MySQLDropDatabaseStatement::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\MySql\MySQLDropDefinition::class, 'database'))->getReturnType()?->getName()
        );
        self::assertSame(
            \Assegai\Orm\Queries\PostgreSql\PostgreSQLDropTableStatement::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\PostgreSql\PostgreSQLDropDefinition::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            \Assegai\Orm\Queries\PostgreSql\PostgreSQLDropDatabaseStatement::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\PostgreSql\PostgreSQLDropDefinition::class, 'database'))->getReturnType()?->getName()
        );
        self::assertSame(
            \Assegai\Orm\Queries\SQLite\SQLiteDropTableStatement::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\SQLite\SQLiteDropDefinition::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            MariaDbDropTableStatement::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\MariaDb\MariaDbDropDefinition::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            MariaDbDropDatabaseStatement::class,
            (new ReflectionMethod(\Assegai\Orm\Queries\MariaDb\MariaDbDropDefinition::class, 'database'))->getReturnType()?->getName()
        );
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

    public function testMySqlDropTableUsesQuotedIdentifier(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL)->switchToMysql();

        $query
            ->drop()
            ->table('users');

        self::assertSame('DROP TABLE IF EXISTS `users`', $query->queryString());
    }

    public function testMariaDbDropTableUsesQuotedIdentifier(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb();

        $query
            ->drop()
            ->table('users');

        self::assertSame('DROP TABLE IF EXISTS `users`', $query->queryString());
    }
    public function testDialectSpecificUpdateBuildersExposeOnlySupportedApiShapes(): void
    {
        $mysqlQuery = $this->createQuery(SQLDialect::MYSQL)->switchToMysql();
        $postgresQuery = $this->createQuery(SQLDialect::MYSQL)->switchToPostgres();
        $sqliteQuery = $this->createQuery(SQLDialect::MYSQL)->switchToSqlite();
        $mariaDbQuery = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb();

        $mysqlUpdate = $mysqlQuery->update('users', lowPriority: true, ignore: true);
        $postgresUpdate = $postgresQuery->update('users');
        $sqliteUpdate = $sqliteQuery->update('users');
        $mariaDbUpdate = $mariaDbQuery->update('users', lowPriority: true, ignore: true);

        self::assertInstanceOf(MySQLUpdateDefinition::class, $mysqlUpdate);
        self::assertInstanceOf(PostgreSQLUpdateDefinition::class, $postgresUpdate);
        self::assertInstanceOf(\Assegai\Orm\Queries\SQLite\SQLiteUpdateDefinition::class, $sqliteUpdate);
        self::assertInstanceOf(MariaDbUpdateDefinition::class, $mariaDbUpdate);
        self::assertSame(3, (new ReflectionMethod(MySQLQuery::class, 'update'))->getNumberOfParameters());
        self::assertSame(1, (new ReflectionMethod(PostgreSQLQuery::class, 'update'))->getNumberOfParameters());
        self::assertSame(1, (new ReflectionMethod(\Assegai\Orm\Queries\SQLite\SQLiteQuery::class, 'update'))->getNumberOfParameters());
        self::assertSame(3, (new ReflectionMethod(MariaDbQuery::class, 'update'))->getNumberOfParameters());
        self::assertSame('UPDATE LOW_PRIORITY IGNORE `users`', $mysqlQuery->queryString());
        self::assertSame('UPDATE "users"', $postgresQuery->queryString());
        self::assertSame('UPDATE "users"', $sqliteQuery->queryString());
        self::assertSame('UPDATE LOW_PRIORITY IGNORE `users`', $mariaDbQuery->queryString());
    }

    public function testMysqlFamilyQueryRootsKeepTypedReturnSignaturesForFamilyOnlyEntryPoints(): void
    {
        self::assertSame(MySQLUseStatement::class, (new ReflectionMethod(MySQLQuery::class, 'use'))->getReturnType()?->getName());
        self::assertSame(MySQLUpdateDefinition::class, (new ReflectionMethod(MySQLQuery::class, 'update'))->getReturnType()?->getName());
        self::assertSame(MariaDbUseStatement::class, (new ReflectionMethod(MariaDbQuery::class, 'use'))->getReturnType()?->getName());
        self::assertSame(MariaDbUpdateDefinition::class, (new ReflectionMethod(MariaDbQuery::class, 'update'))->getReturnType()?->getName());
    }

    public function testDialectSpecificUpdateSetChainsStayTyped(): void
    {
        $mysqlAssignmentList = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMysql()
            ->update('users')
            ->set(['name' => 'Ada']);
        $postgresAssignmentList = $this->createQuery(SQLDialect::MYSQL)
            ->switchToPostgres()
            ->update('users')
            ->set(['name' => 'Ada']);
        $sqliteAssignmentList = $this->createQuery(SQLDialect::MYSQL)
            ->switchToSqlite()
            ->update('users')
            ->set(['name' => 'Ada']);
        $mariaDbAssignmentList = $this->createQuery(SQLDialect::MYSQL)
            ->switchToMariaDb()
            ->update('users')
            ->set(['name' => 'Ada']);

        self::assertInstanceOf(MySQLAssignmentList::class, $mysqlAssignmentList);
        self::assertInstanceOf(PostgreSQLAssignmentList::class, $postgresAssignmentList);
        self::assertInstanceOf(SQLiteAssignmentList::class, $sqliteAssignmentList);
        self::assertInstanceOf(MariaDbAssignmentList::class, $mariaDbAssignmentList);

        self::assertInstanceOf(MySQLWhereClause::class, $mysqlAssignmentList->where(['id' => 1]));
        self::assertInstanceOf(PostgreSQLWhereClause::class, $postgresAssignmentList->where(['id' => 1]));
        self::assertInstanceOf(SQLiteWhereClause::class, $sqliteAssignmentList->where(['id' => 1]));
        self::assertInstanceOf(MariaDbWhereClause::class, $mariaDbAssignmentList->where(['id' => 1]));
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

    public function testColumnDefinitionFactoryReturnsDialectSpecificBuilders(): void
    {
        $mysqlColumn = SQLColumnDefinition::forDialect('name', ColumnType::VARCHAR, 255, nullable: false, dialect: SQLDialect::MYSQL);
        $postgresColumn = SQLColumnDefinition::forDialect('name', ColumnType::VARCHAR, 255, nullable: false, dialect: SQLDialect::POSTGRESQL);
        $sqliteColumn = SQLColumnDefinition::forDialect('name', ColumnType::VARCHAR, 255, nullable: false, dialect: SQLDialect::SQLITE);
        $mariaDbColumn = SQLColumnDefinition::forDialect('name', ColumnType::VARCHAR, 255, nullable: false, dialect: SQLDialect::MARIADB);

        self::assertInstanceOf(MySQLColumnDefinition::class, $mysqlColumn);
        self::assertInstanceOf(PostgreSQLColumnDefinition::class, $postgresColumn);
        self::assertInstanceOf(SQLiteColumnDefinition::class, $sqliteColumn);
        self::assertInstanceOf(MariaDbColumnDefinition::class, $mariaDbColumn);

        self::assertSame('`name` VARCHAR(255) NOT NULL', $mysqlColumn->queryString());
        self::assertSame('"name" VARCHAR(255) NOT NULL', $postgresColumn->queryString());
        self::assertSame('`name` TEXT NOT NULL', $sqliteColumn->queryString());
        self::assertSame('`name` VARCHAR(255) NOT NULL', $mariaDbColumn->queryString());
        self::assertSame('VARCHAR', $mysqlColumn->getTypeExpression());
        self::assertSame('VARCHAR(255)', $postgresColumn->getTypeExpression());
        self::assertSame('TEXT', $sqliteColumn->getTypeExpression());
        self::assertSame('VARCHAR', $mariaDbColumn->getTypeExpression());
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

    public function testMariaDbMultipleInsertStaysOnMariaDbBuilderPath(): void
    {
        $query = $this->createQuery(SQLDialect::MYSQL)->switchToMariaDb();

        $statement = $query
            ->insertInto('users')
            ->multipleRows(['name', 'email']);

        self::assertInstanceOf(MariaDbInsertIntoMultipleStatement::class, $statement);
        self::assertTrue(method_exists($statement, 'onDuplicateKeyUpdate'));
        self::assertFalse(method_exists($statement, 'returning'));

        $statement->rows([
            ['Ada', 'ada@example.com'],
            ['Bob', 'bob@example.com'],
        ]);

        self::assertSame(
            'INSERT INTO `users` (`name`, `email`) VALUES (?, ?), (?, ?)',
            $query->queryString()
        );
    }

    public function testPostgreSqlMultipleInsertSupportsReturning(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL);

        $statement = $query
            ->insertInto('users')
            ->multipleRows(['name', 'email']);

        self::assertInstanceOf(\Assegai\Orm\Queries\PostgreSql\PostgreSQLInsertIntoMultipleStatement::class, $statement);
        self::assertTrue(method_exists($statement, 'returning'));
        self::assertFalse(method_exists($statement, 'onDuplicateKeyUpdate'));

        $statement
            ->rows([
                ['Ada', 'ada@example.com'],
                ['Bob', 'bob@example.com'],
            ])
            ->returning(['id', 'name']);

        self::assertSame(
            'INSERT INTO "users" ("name", "email") VALUES (?, ?), (?, ?) RETURNING "id", "name"',
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

    public function testDialectSpecificRenameEntryPointsKeepTypedReturnSignatures(): void
    {
        self::assertSame(
            MySQLRenameTableStatement::class,
            (new ReflectionMethod(MySQLRenameStatement::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            PostgreSQLRenameTableStatement::class,
            (new ReflectionMethod(PostgreSQLRenameStatement::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            SQLiteRenameTableStatement::class,
            (new ReflectionMethod(SQLiteRenameStatement::class, 'table'))->getReturnType()?->getName()
        );
        self::assertSame(
            MariaDbRenameTableStatement::class,
            (new ReflectionMethod(MariaDbRenameStatement::class, 'table'))->getReturnType()?->getName()
        );
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
            ->values(['Ada'])
            ->returning(['id', 'name']);
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
