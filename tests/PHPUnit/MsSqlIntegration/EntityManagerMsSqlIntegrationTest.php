<?php

namespace Tests\PHPUnit\MsSqlIntegration;

use Assegai\Orm\Queries\QueryBuilder\Results\InsertResult;
use Tests\PHPUnit\Support\MsSqlIntegrationTestCase;
use Unit\mocks\MockColorType;
use Unit\mocks\MockEntity;

final class EntityManagerMsSqlIntegrationTest extends MsSqlIntegrationTestCase
{
    public function testDataSourceReportsCurrentSqlServerDatabase(): void
    {
        self::assertSame($this->databaseName, $this->dataSource->getDatabaseName());
    }

    public function testInsertPersistsValidEntity(): void
    {
        $entity = new MockEntity();
        $entity->name = 'mssql insert valid';
        $entity->description = 'Inserted through insert()';
        $entity->colorType = MockColorType::BLUE;

        $result = $this->manager->insert(MockEntity::class, $entity);

        self::assertInstanceOf(InsertResult::class, $result);
        self::assertTrue($result->isOk());

        $entityId = (int) ($result->generatedMaps?->id ?? $result->identifiers?->id);
        $row = $this->fetchMocksRowById($entityId);

        self::assertSame('mssql insert valid', $row['name']);
        self::assertSame('Inserted through insert()', $row['description']);
        self::assertSame(MockColorType::BLUE->value, $row['color_type']);
    }

    public function testRemoveDeletesPersistedRow(): void
    {
        $entity = new MockEntity();
        $entity->name = 'mssql remove target';
        $entity->description = 'Deleted through remove()';
        $entity->colorType = MockColorType::YELLOW;

        $insertResult = $this->manager->insert(MockEntity::class, $entity);
        $entity->id = (int) ($insertResult->generatedMaps?->id ?? $insertResult->identifiers?->id);

        $result = $this->manager->remove($entity);

        self::assertTrue($result->isOk());
        self::assertSame(0, $this->rowCount('mocks'));
    }
}
