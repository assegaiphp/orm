<?php

namespace Tests\PHPUnit\MsSqlIntegration;

use Assegai\Orm\DataSource\Schema;
use Tests\PHPUnit\Support\MsSqlIntegrationTestCase;
use Unit\mocks\MockColorType;
use Unit\mocks\MockEntity;

final class SchemaMsSqlIntegrationTest extends MsSqlIntegrationTestCase
{
    public function testCreateIfNotExistsCreatesMocksTableWithExpectedColumns(): void
    {
        Schema::dropIfExists(MockEntity::class, $this->schemaOptions);

        self::assertTrue(Schema::createIfNotExists(MockEntity::class, $this->schemaOptions));
        self::assertTrue(Schema::exists('mocks', $this->dataSource));
        self::assertTrue(Schema::hasColumns('mocks', ['id', 'name', 'description', 'color_type'], $this->dataSource));
    }

    public function testInfoReturnsSqlServerStyleTableDefinition(): void
    {
        $info = Schema::info(MockEntity::class, $this->schemaOptions);

        self::assertNotNull($info);
        self::assertStringContainsString('CREATE TABLE [mocks]', $info->ddlStatement);
        self::assertStringContainsString('[id] int IDENTITY(1,1) NOT NULL PRIMARY KEY', $info->ddlStatement);
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
            $this->dataSource->getClient()->exec("IF OBJECT_ID(N'[socks]', N'U') IS NOT NULL DROP TABLE [socks]");
            Schema::createIfNotExists(MockEntity::class, $this->schemaOptions);
        }
    }

    public function testTruncateRemovesPersistedRows(): void
    {
        $entity = new MockEntity();
        $entity->name = 'mssql schema truncate';
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
