<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Attributes\SqlEntityOptions;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Management\Inspectors\EntityInspector;
use PHPUnit\Framework\TestCase;
use Unit\mocks\MockEntity;
use Unit\mocks\MySqlEngineEntity;
use Unit\mocks\NotAMockEntity;

require_once __DIR__ . '/../../Unit/mocks/MySqlEngineEntity.php';

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

    public function testGetSqlOptionsReturnsCompanionAttributeWhenPresent(): void
    {
        $sqlOptions = $this->inspector->getSqlOptions(new MySqlEngineEntity());

        self::assertInstanceOf(SqlEntityOptions::class, $sqlOptions);
        self::assertSame('MyISAM', $sqlOptions?->engine);
    }

    public function testGetColumnsReturnsEntityColumnsAndRespectsExclusions(): void
    {
        $columns = $this->inspector->getColumns($this->entity);

        self::assertArrayHasKey('id', $columns);
        self::assertArrayHasKey('colorType', $columns);
        self::assertSame('mocks.name', $columns['name']);
        self::assertSame('mocks.description', $columns['description']);
        self::assertSame('mocks.color_type', $columns['colorType']);
        self::assertSame('mocks.created_at', $columns['createdAt']);
        self::assertSame('mocks.updated_at', $columns['updatedAt']);
        self::assertSame('mocks.deleted_at', $columns['deletedAt']);
        self::assertArrayNotHasKey('rank', $columns);

        $excludedColumns = $this->inspector->getColumns($this->entity, ['id']);

        self::assertArrayNotHasKey('id', $excludedColumns);
        self::assertSame('mocks.name', $excludedColumns['name']);
    }


    public function testGetColumnsConvertsImplicitCamelCasePropertyNamesToSnakeCase(): void
    {
        $entity = new #[\Assegai\Orm\Attributes\Entity(table: 'screenings')] class () {
            #[\Assegai\Orm\Attributes\Columns\Column(type: \Assegai\Orm\Queries\Sql\ColumnType::BOOLEAN)]
            public bool $isNowShowing = true;
        };

        $columns = $this->inspector->getColumns($entity);

        self::assertSame('screenings.is_now_showing', $columns['isNowShowing']);
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
