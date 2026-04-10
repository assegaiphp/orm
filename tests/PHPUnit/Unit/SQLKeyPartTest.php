<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Enumerations\SQLDialect;
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
}
