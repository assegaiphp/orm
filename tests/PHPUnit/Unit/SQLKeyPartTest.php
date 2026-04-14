<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\MariaDb\MariaDbKeyPart;
use Assegai\Orm\Queries\MySql\MySQLKeyPart;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLKeyPart;
use Assegai\Orm\Queries\SQLite\SQLiteKeyPart;
use Assegai\Orm\Queries\Sql\SQLKeyPart;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SQLKeyPartTest extends TestCase
{
    public function testQuotesSafeIdentifiersForMySql(): void
    {
        $keyPart = new SQLKeyPart('users.created_at', ascending: false);

        self::assertSame('`users`.`created_at` DESC', (string) $keyPart);
    }

    public function testQuotesSafeIdentifiersForPostgreSql(): void
    {
        $keyPart = new SQLKeyPart('users.created_at', ascending: false, dialect: SQLDialect::POSTGRESQL);

        self::assertSame('"users"."created_at" DESC', (string) $keyPart);
    }

    public function testRejectsUnsafeIdentifiers(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsafe SQL identifier: name; DROP TABLE users');

        new SQLKeyPart('name; DROP TABLE users');
    }

    public function testFactoryReturnsDialectSpecificBuilders(): void
    {
        $mysqlKeyPart = SQLKeyPart::forDialect('users.created_at', ascending: false, dialect: SQLDialect::MYSQL);
        $postgresKeyPart = SQLKeyPart::forDialect('users.created_at', ascending: false, dialect: SQLDialect::POSTGRESQL);
        $sqliteKeyPart = SQLKeyPart::forDialect('users.created_at', ascending: false, dialect: SQLDialect::SQLITE);
        $mariaDbKeyPart = SQLKeyPart::forDialect('users.created_at', ascending: false, dialect: SQLDialect::MARIADB);

        self::assertInstanceOf(MySQLKeyPart::class, $mysqlKeyPart);
        self::assertInstanceOf(PostgreSQLKeyPart::class, $postgresKeyPart);
        self::assertInstanceOf(SQLiteKeyPart::class, $sqliteKeyPart);
        self::assertInstanceOf(MariaDbKeyPart::class, $mariaDbKeyPart);

        self::assertSame('`users`.`created_at` DESC', (string) $mysqlKeyPart);
        self::assertSame('"users"."created_at" DESC', (string) $postgresKeyPart);
        self::assertSame('"users"."created_at" DESC', (string) $sqliteKeyPart);
        self::assertSame('`users`.`created_at` DESC', (string) $mariaDbKeyPart);
    }
}
