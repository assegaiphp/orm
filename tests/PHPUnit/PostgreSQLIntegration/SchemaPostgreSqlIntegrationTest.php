<?php

namespace Tests\PHPUnit\PostgreSQLIntegration;

use Assegai\Orm\DataSource\Schema;
use Tests\PHPUnit\Support\PostgreSqlIntegrationTestCase;
use Unit\mocks\AlteredMockEntity;
use Unit\mocks\MockColorType;
use Unit\mocks\MockEntity;

final class SchemaPostgreSqlIntegrationTest extends PostgreSqlIntegrationTestCase
{
    public function testCreateIfNotExistsCreatesMocksTableWithExpectedColumns(): void
    {
        Schema::dropIfExists(MockEntity::class, $this->schemaOptions);

        self::assertTrue(Schema::createIfNotExists(MockEntity::class, $this->schemaOptions));
        self::assertTrue(Schema::exists('mocks', $this->dataSource));
        self::assertTrue(Schema::hasColumns('mocks', ['id', 'name', 'description', 'color_type'], $this->dataSource));
    }

    public function testInfoReturnsPostgreSqlTableDefinition(): void
    {
        $info = Schema::info(MockEntity::class, $this->schemaOptions);

        self::assertNotNull($info);
        self::assertStringContainsString('CREATE TABLE "mocks"', $info->ddlStatement);
        self::assertStringContainsString('"id" bigint NOT NULL', $info->ddlStatement);
        self::assertStringContainsString('"name" character varying(255) NOT NULL UNIQUE', $info->ddlStatement);
        self::assertNotEmpty($info->tableFields);
    }

    public function testRenameChangesTableName(): void
    {
        self::assertTrue(Schema::exists('mocks', $this->dataSource));

        try {
            self::assertTrue(Schema::rename('mocks', 'socks', $this->schemaOptions));
            self::assertTrue(Schema::exists('socks', $this->dataSource));
            self::assertFalse(Schema::exists('mocks', $this->dataSource));
        } finally {
            $this->dataSource->getClient()->exec('DROP TABLE IF EXISTS "socks"');
            Schema::createIfNotExists(MockEntity::class, $this->schemaOptions);
        }
    }

    public function testAlterRebuildsTableWithAlteredEntityShape(): void
    {
        self::assertTrue(Schema::alter(AlteredMockEntity::class, $this->schemaOptions));
        self::assertTrue(Schema::hasColumns('mocks', ['id', 'name', 'email'], $this->dataSource));
        self::assertFalse(Schema::hasColumns('mocks', ['description', 'color_type'], $this->dataSource));

        $statement = $this->dataSource->getClient()->prepare(
            'INSERT INTO "mocks" ("name", "email") VALUES (:name, :email)'
        );
        $statement->execute([
            'name' => 'Altered pgsql row',
            'email' => 'altered@example.com',
        ]);

        $row = $this->dataSource->getClient()
            ->query('SELECT "name", "email" FROM "mocks" ORDER BY "id" DESC LIMIT 1')
            ->fetch();

        self::assertSame('Altered pgsql row', $row['name']);
        self::assertSame('altered@example.com', $row['email']);
    }

    public function testAlterResetsIdentitySequenceAfterCopyingExistingRows(): void
    {
        $this->dataSource->getClient()->exec(
            <<<SQL
INSERT INTO "mocks" ("name", "description", "color_type")
VALUES
  ('pgsql alter existing row 1', 'before alter 1', 'violet'),
  ('pgsql alter existing row 2', 'before alter 2', 'green')
SQL
        );

        self::assertTrue(Schema::alter(AlteredMockEntity::class, $this->schemaOptions));

        $statement = $this->dataSource->getClient()->prepare(
            'INSERT INTO "mocks" ("name", "email") VALUES (:name, :email) RETURNING "id"'
        );
        $statement->execute([
            'name' => 'pgsql alter new row',
            'email' => 'after-alter@example.com',
        ]);

        $newId = (int) $statement->fetchColumn();

        self::assertGreaterThan(2, $newId);
    }

    public function testTruncateRemovesPersistedRows(): void
    {
        $entity = new MockEntity();
        $entity->name = 'pgsql schema truncate';
        $entity->description = 'Inserted before truncate';
        $entity->colorType = MockColorType::VIOLET;

        $insertResult = $this->manager->insert(MockEntity::class, $entity);

        self::assertTrue($insertResult->isOk());
        self::assertSame(1, $this->rowCount('mocks'));
        self::assertTrue(Schema::truncate(MockEntity::class, $this->schemaOptions));
        self::assertSame(0, $this->rowCount('mocks'));
    }

    public function testDropIfExistsRemovesMocksTable(): void
    {
        self::assertTrue(Schema::exists('mocks', $this->dataSource));
        self::assertTrue(Schema::dropIfExists(MockEntity::class, $this->schemaOptions));
        self::assertFalse(Schema::exists('mocks', $this->dataSource));
    }
}
