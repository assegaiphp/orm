<?php

namespace Tests\PHPUnit\PostgreSQLIntegration;

use Assegai\Orm\Management\Options\UpsertOptions;
use Assegai\Orm\Queries\QueryBuilder\Results\InsertResult;
use Tests\PHPUnit\Support\PostgreSqlIntegrationTestCase;
use Unit\mocks\MockColorType;
use Unit\mocks\MockEntity;

final class EntityManagerPostgreSqlIntegrationTest extends PostgreSqlIntegrationTestCase
{
    public function testInsertReturnsHydratedGeneratedMaps(): void
    {
        $entity = new MockEntity();
        $entity->name = 'pgsql insert valid';
        $entity->description = 'Inserted through insert()';
        $entity->colorType = MockColorType::BLUE;

        $result = $this->manager->insert(MockEntity::class, $entity);

        self::assertInstanceOf(InsertResult::class, $result);
        self::assertTrue($result->isOk());
        self::assertStringContainsString('RETURNING "id" AS "id"', (string) $result->getRaw());
        self::assertInstanceOf(MockEntity::class, $result->getGeneratedMaps());

        $entityId = (int) ($result->generatedMaps?->id ?? $result->identifiers?->id);
        $row = $this->fetchMocksRowById($entityId);

        self::assertSame('pgsql insert valid', $row['name']);
        self::assertSame('Inserted through insert()', $row['description']);
        self::assertSame(MockColorType::BLUE->value, $row['color_type']);
        self::assertSame(MockColorType::BLUE, $result->getGeneratedMaps()?->colorType);
    }

    public function testUpdateReturnsHydratedGeneratedMaps(): void
    {
        $entity = new MockEntity();
        $entity->name = 'pgsql update target';
        $entity->description = 'Before update';
        $entity->colorType = MockColorType::GREEN;

        $insertResult = $this->manager->insert(MockEntity::class, $entity);
        $entityId = (int) ($insertResult->generatedMaps?->id ?? $insertResult->identifiers?->id);

        $result = $this->manager->update(
            MockEntity::class,
            ['description' => 'After update', 'colorType' => MockColorType::VIOLET],
            ['id' => $entityId],
        );

        self::assertTrue($result->isOk());
        self::assertStringContainsString('RETURNING "id" AS "id"', (string) $result->getRaw());
        self::assertSame($entityId, (int) ($result->identifiers?->id ?? 0));
        self::assertInstanceOf(MockEntity::class, $result->generatedMaps);
        self::assertSame('After update', $result->generatedMaps?->description);
        self::assertSame(MockColorType::VIOLET, $result->generatedMaps?->colorType);
    }

    public function testUpsertPreservesIdentifierOnConflict(): void
    {
        $entity = new MockEntity();
        $entity->name = 'pgsql upsert subject';
        $entity->description = 'Initial insert';
        $entity->colorType = MockColorType::ORANGE;

        $insertResult = $this->manager->insert(MockEntity::class, $entity);
        $entityId = (int) ($insertResult->generatedMaps?->id ?? $insertResult->identifiers?->id);

        $entity->id = $entityId;
        $entity->description = 'Updated through pgsql upsert';

        $upsertResult = $this->manager->upsert(
            MockEntity::class,
            $entity,
            ['id'],
            new UpsertOptions(skipUpdateIfNoValuesChanged: false),
        );

        self::assertTrue($upsertResult->isOk());
        self::assertStringContainsString('ON CONFLICT ("id") DO UPDATE SET', (string) $upsertResult->getRaw());
        self::assertStringContainsString('RETURNING "id" AS "id"', (string) $upsertResult->getRaw());
        self::assertSame($entityId, (int) ($upsertResult->identifiers?->id ?? 0));

        $row = $this->fetchMocksRowById($entityId);

        self::assertSame('Updated through pgsql upsert', $row['description']);
    }

    public function testRemoveUsesPrimaryKeyMetadataAndDeletesRow(): void
    {
        $entity = new MockEntity();
        $entity->name = 'pgsql remove target';
        $entity->description = 'Delete through remove()';
        $entity->colorType = MockColorType::YELLOW;

        $insertResult = $this->manager->insert(MockEntity::class, $entity);
        $entity->id = (int) ($insertResult->generatedMaps?->id ?? $insertResult->identifiers?->id);

        $result = $this->manager->remove($entity);

        self::assertTrue($result->isOk());
        self::assertStringContainsString('RETURNING "id"', (string) $result->getRaw());
        self::assertSame(1, $result->getTotalAffectedRows());
        self::assertSame(0, $this->rowCount('mocks'));
    }
}
