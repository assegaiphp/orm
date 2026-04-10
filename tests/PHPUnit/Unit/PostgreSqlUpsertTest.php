<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Queries\Sql\SQLQuery;
use PDO;
use PHPUnit\Framework\TestCase;
use Unit\mocks\MockColorType;
use Unit\mocks\MockEntity;

final class PostgreSqlUpsertTest extends TestCase
{
    private ?DataSource $dataSource = null;
    private string $databasePath;

    protected function setUp(): void
    {
        $this->databasePath = sys_get_temp_dir() . '/assegai-postgresql-upsert-' . uniqid('', true) . '.sqlite';
        $this->cleanupSqliteFiles($this->databasePath);
        $this->dataSource = new DataSource(new DataSourceOptions(
            entities: [MockEntity::class],
            name: $this->databasePath,
            type: DataSourceType::SQLITE,
        ));

        $this->dataSource->getClient()->exec(
            'CREATE TABLE mocks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                description TEXT NOT NULL,
                color_type TEXT NOT NULL,
                created_at TEXT NULL,
                updated_at TEXT NULL,
                deleted_at TEXT NULL
            )'
        );
    }

    protected function tearDown(): void
    {
        $this->dataSource?->disconnect();
        $this->cleanupSqliteFiles($this->databasePath);
    }

    public function testInsertUsesReturningToHydrateGeneratedMaps(): void
    {
        $manager = $this->createPostgreSqlManager();

        $entity = new MockEntity();
        $entity->name = 'Insert returning';
        $entity->description = 'Inserted through PostgreSQL path';
        $entity->colorType = MockColorType::BLUE;

        $result = $manager->insert(MockEntity::class, $entity);

        self::assertTrue($result->isOk());
        self::assertStringContainsString('RETURNING "id" AS "id"', $result->getRaw());
        self::assertSame(1, $result->getIdentifiers()?->id);
        self::assertInstanceOf(MockEntity::class, $result->getGeneratedMaps());
        self::assertSame('Insert returning', $result->getGeneratedMaps()?->name);
        self::assertSame(MockColorType::BLUE, $result->getGeneratedMaps()?->colorType);
    }

    public function testUpdateUsesReturningToHydrateUpdatedRow(): void
    {
        $manager = $this->createPostgreSqlManager();

        $entity = new MockEntity();
        $entity->name = 'Update returning';
        $entity->description = 'Before update';
        $entity->colorType = MockColorType::ORANGE;

        $insertResult = $manager->insert(MockEntity::class, $entity);
        $entityId = $insertResult->getIdentifiers()?->id;

        $result = $manager->update(
            MockEntity::class,
            ['description' => 'After update', 'colorType' => MockColorType::VIOLET],
            ['id' => $entityId],
        );

        self::assertTrue($result->isOk());
        self::assertStringContainsString('RETURNING "id" AS "id"', $result->getRaw());
        self::assertSame($entityId, $result->getIdentifiers()?->id);
        self::assertInstanceOf(MockEntity::class, $result->getGeneratedMaps());
        self::assertSame('After update', $result->getGeneratedMaps()?->description);
        self::assertSame(MockColorType::VIOLET, $result->getGeneratedMaps()?->colorType);
    }

    public function testRemoveUsesReturningOnPostgreSqlPath(): void
    {
        $manager = $this->createPostgreSqlManager();

        $entity = new MockEntity();
        $entity->name = 'Delete returning';
        $entity->description = 'Delete me';
        $entity->colorType = MockColorType::YELLOW;

        $insertResult = $manager->insert(MockEntity::class, $entity);
        $entity->id = $insertResult->getIdentifiers()?->id;

        $result = $manager->remove($entity);

        self::assertTrue($result->isOk());
        self::assertStringContainsString('RETURNING "id"', $result->getRaw());
        self::assertSame(1, $result->getTotalAffectedRows());
    }

    public function testUpsertUsesPostgreSqlConflictSyntaxAndPreservesIdentifiers(): void
    {
        $manager = $this->createPostgreSqlManager();

        $first = new MockEntity();
        $first->name = 'Ada';
        $first->description = 'Initial record';
        $first->colorType = MockColorType::RED;

        $insertResult = $manager->upsert(MockEntity::class, $first, ['name']);

        self::assertTrue($insertResult->isOk());
        self::assertStringContainsString('ON CONFLICT ("name") DO UPDATE SET', $insertResult->getRaw());
        self::assertStringContainsString('RETURNING "id" AS "id"', $insertResult->getRaw());
        self::assertSame(1, $insertResult->getIdentifiers()?->id);

        $second = new MockEntity();
        $second->name = 'Ada';
        $second->description = 'Updated record';
        $second->colorType = MockColorType::GREEN;

        $updateResult = $manager->upsert(MockEntity::class, $second, ['name']);

        self::assertTrue($updateResult->isOk());
        self::assertSame($insertResult->getIdentifiers()?->id, $updateResult->getIdentifiers()?->id);
        self::assertIsObject($updateResult->getGeneratedMaps());
        self::assertSame('Updated record', $updateResult->getGeneratedMaps()?->description);
        self::assertContains($updateResult->getGeneratedMaps()?->colorType, [MockColorType::GREEN, MockColorType::GREEN->value]);
    }

    private function createPostgreSqlManager(): EntityManager
    {
        return new EntityManager(
            connection: $this->dataSource,
            query: new SQLQuery(
                db: $this->dataSource->getClient(),
                fetchClass: MockEntity::class,
                fetchMode: PDO::FETCH_CLASS,
                dialect: SQLDialect::POSTGRESQL,
            ),
        );
    }

    private function cleanupSqliteFiles(string $path): void
    {
        foreach ([$path, $path . '-wal', $path . '-shm'] as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
