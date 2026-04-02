<?php

namespace Tests\PHPUnit\MySQLIntegration;

use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Management\Options\UpsertOptions;
use Assegai\Orm\Queries\QueryBuilder\Results\InsertResult;
use Tests\PHPUnit\Support\MySqlIntegrationTestCase;
use Unit\mocks\MockColorType;
use Unit\mocks\MockEntity;
use Unit\mocks\NotAMockEntity;

final class EntityManagerMySqlIntegrationTest extends MySqlIntegrationTestCase
{
    public function testValidateEntityNameRejectsClassesWithoutEntityAttribute(): void
    {
        $this->manager->validateEntityName(MockEntity::class);
        self::addToAssertionCount(1);

        $this->expectException(ORMException::class);
        $this->manager->validateEntityName(NotAMockEntity::class);
    }

    public function testSaveInsertsNewEntity(): void
    {
        $entity = new MockEntity();
        $entity->name = 'mysql save insert';
        $entity->description = 'Inserted through save()';
        $entity->colorType = MockColorType::RED;

        $result = $this->manager->save($entity);

        self::assertInstanceOf(InsertResult::class, $result);
        self::assertTrue($result->isOk());

        $entityId = $result->generatedMaps?->id ?? $result->identifiers?->id;

        self::assertNotNull($entityId);

        $row = $this->fetchMocksRowById((int) $entityId);

        self::assertSame('mysql save insert', $row['name']);
        self::assertSame('Inserted through save()', $row['description']);
        self::assertSame(MockColorType::RED->value, $row['color_type']);
    }

    public function testSaveInsertsWhenPrimaryKeyRowDoesNotExist(): void
    {
        $entity = new MockEntity();
        $entity->id = 1234567;
        $entity->name = 'mysql save missing row';
        $entity->description = 'Inserted even though a primary key was provided';
        $entity->colorType = MockColorType::BLUE;

        $result = $this->manager->save($entity);

        self::assertInstanceOf(InsertResult::class, $result);
        self::assertTrue($result->isOk());

        $entityId = $result->generatedMaps?->id ?? $result->identifiers?->id;
        $row = $this->fetchMocksRowById((int) $entityId);

        self::assertSame('mysql save missing row', $row['name']);
        self::assertSame('Inserted even though a primary key was provided', $row['description']);
        self::assertSame(MockColorType::BLUE->value, $row['color_type']);
    }

    public function testCreateHydratesEntityFromDtoObject(): void
    {
        $dto = (object) [
            'name' => 'dto hydrated entity',
            'description' => 'Hydrated without manually mapping array keys',
            'colorType' => MockColorType::GREEN,
        ];

        $entity = $this->manager->create(MockEntity::class, $dto);

        self::assertInstanceOf(MockEntity::class, $entity);
        self::assertSame('dto hydrated entity', $entity->name);
        self::assertSame('Hydrated without manually mapping array keys', $entity->description);
        self::assertSame(MockColorType::GREEN, $entity->colorType);
        self::assertTrue(property_exists($entity, 'createdAt'));
        self::assertTrue(property_exists($entity, 'updatedAt'));
        self::assertTrue(property_exists($entity, 'deletedAt'));
    }

    public function testInsertPersistsValidEntity(): void
    {
        $entity = new MockEntity();
        $entity->name = 'mysql insert valid';
        $entity->description = 'Inserted through insert()';
        $entity->colorType = MockColorType::YELLOW;

        $result = $this->manager->insert(MockEntity::class, $entity);

        self::assertInstanceOf(InsertResult::class, $result);
        self::assertTrue($result->isOk());

        $entityId = (int) ($result->generatedMaps?->id ?? $result->identifiers?->id);
        $row = $this->fetchMocksRowById($entityId);

        self::assertSame('mysql insert valid', $row['name']);
        self::assertSame('Inserted through insert()', $row['description']);
        self::assertSame(MockColorType::YELLOW->value, $row['color_type']);
    }

    public function testInsertReturnsErrorForInvalidStructure(): void
    {
        $invalid = (object) [
            'title' => 'invalid structure',
            'desc' => 'does not map to MockEntity',
        ];

        $result = $this->manager->insert(MockEntity::class, $invalid);

        self::assertTrue($result->isError());
        self::assertNotEmpty($result->getErrors());
        self::assertSame(0, $this->rowCount('mocks'));
    }

    public function testUpsertPreservesExistingIdentifierOnConflict(): void
    {
        $entity = new MockEntity();
        $entity->name = 'mysql upsert subject';
        $entity->description = 'Initial insert';
        $entity->colorType = MockColorType::ORANGE;

        $insertResult = $this->manager->insert(MockEntity::class, $entity);

        self::assertTrue($insertResult->isOk());

        $entityId = (int) ($insertResult->generatedMaps?->id ?? $insertResult->identifiers?->id);
        $entity->id = $entityId;
        $entity->description = 'Updated through mysql upsert';

        $upsertResult = $this->manager->upsert(
            MockEntity::class,
            $entity,
            ['id'],
            new UpsertOptions(skipUpdateIfNoValuesChanged: false),
        );

        self::assertTrue($upsertResult->isOk());
        self::assertSame($entityId, $entity->id);
        self::assertSame($entityId, $upsertResult->identifiers?->id);
        self::assertSame($entityId, $upsertResult->generatedMaps?->id);

        $row = $this->fetchMocksRowById($entityId);

        self::assertSame('Updated through mysql upsert', $row['description']);
        self::assertSame(MockColorType::ORANGE->value, $row['color_type']);
    }

    public function testSoftRemoveSetsDeletedAtTimestamp(): void
    {
        $entity = new MockEntity();
        $entity->name = 'mysql soft remove';
        $entity->description = 'Soft removed through entity manager';
        $entity->colorType = MockColorType::VIOLET;

        $insertResult = $this->manager->insert(MockEntity::class, $entity);
        $entityId = (int) ($insertResult->generatedMaps?->id ?? $insertResult->identifiers?->id);
        $entity->id = $entityId;

        $result = $this->manager->softRemove($entity);

        self::assertTrue($result->isOk());

        $statement = $this->dataSource->getClient()->prepare('SELECT `deleted_at` FROM `mocks` WHERE `id` = :id');
        $statement->execute(['id' => $entityId]);
        $deletedAt = $statement->fetchColumn();

        self::assertIsString($deletedAt);
        self::assertNotSame('', $deletedAt);
    }
}
