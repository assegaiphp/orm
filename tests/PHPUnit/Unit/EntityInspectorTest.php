<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Management\Inspectors\EntityInspector;
use PHPUnit\Framework\TestCase;
use Unit\mocks\MockEntity;
use Unit\mocks\NotAMockEntity;

final class EntityInspectorTest extends TestCase
{
    private EntityInspector $inspector;
    private MockEntity $entity;
    private NotAMockEntity $invalidEntity;

    protected function setUp(): void
    {
        $this->inspector = EntityInspector::getInstance();
        $this->entity = new MockEntity();
        $this->invalidEntity = new NotAMockEntity();
    }

    public function testGetInstanceReturnsSingleton(): void
    {
        self::assertInstanceOf(EntityInspector::class, $this->inspector);
        self::assertSame($this->inspector, EntityInspector::getInstance());
    }

    public function testValidateEntityNameRejectsClassesWithoutEntityAttribute(): void
    {
        $this->inspector->validateEntityName(MockEntity::class);
        self::addToAssertionCount(1);

        $this->expectException(ORMException::class);
        $this->inspector->validateEntityName(NotAMockEntity::class);
    }

    public function testGetMetaDataReturnsConfiguredEntityMetadata(): void
    {
        $metadata = $this->inspector->getMetaData($this->entity);

        self::assertSame('mocks', $metadata->table);
        self::assertSame('assegai_test_db', $metadata->database);
    }

    public function testGetColumnsReturnsEntityColumnsAndRespectsExclusions(): void
    {
        $columns = $this->inspector->getColumns($this->entity);

        self::assertArrayHasKey('id', $columns);
        self::assertArrayHasKey('colorType', $columns);
        self::assertContains('mocks.name', $columns);
        self::assertContains('mocks.description', $columns);
        self::assertContains('mocks.created_at', $columns);
        self::assertContains('mocks.updated_at', $columns);
        self::assertContains('mocks.deleted_at', $columns);
        self::assertArrayNotHasKey('rank', $columns);

        $excludedColumns = $this->inspector->getColumns($this->entity, ['id']);

        self::assertArrayNotHasKey('id', $excludedColumns);
        self::assertContains('mocks.name', $excludedColumns);
    }

    public function testGetValuesReturnsEntityPropertyValues(): void
    {
        $this->entity->name = 'Shaka';

        $entityValues = $this->inspector->getValues($this->entity);

        self::assertContains('Shaka', $entityValues);
        self::assertNotContains('Caesar', $entityValues);
    }

    public function testGetTableNameReturnsConfiguredTableName(): void
    {
        self::assertSame('mocks', $this->inspector->getTableName($this->entity));
    }

    public function testGetTableNameRejectsObjectsWithoutEntityAttribute(): void
    {
        $this->expectException(ORMException::class);
        $this->inspector->getTableName($this->invalidEntity);
    }
}
