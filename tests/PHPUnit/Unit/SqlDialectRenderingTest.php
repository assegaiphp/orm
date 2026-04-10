<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\Sql\SQLQuery;
use PDO;
use PHPUnit\Framework\TestCase;

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

    public function testPostgreSqlUpdateBuilderIgnoresMysqlOnlyModifiers(): void
    {
        $query = $this->createQuery(SQLDialect::POSTGRESQL);

        $query
            ->update('users', lowPriority: true, ignore: true)
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

        $result = $query
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
        return new SQLQuery(new PDO('sqlite::memory:'), dialect: $dialect);
    }
}
