<?php

namespace Tests\PHPUnit\MySQLIntegration;

use Assegai\Orm\DataSource\Schema;
use Tests\PHPUnit\Support\MySqlIntegrationTestCase;
use Unit\mocks\AlteredMockEntity;
use Unit\mocks\MockColorType;
use Unit\mocks\MockEntity;

final class SchemaMySqlIntegrationTest extends MySqlIntegrationTestCase
{
    public function testCreateIfNotExistsCreatesMocksTableWithExpectedColumns(): void
    {
        Schema::dropIfExists(MockEntity::class, $this->schemaOptions);

        self::assertTrue(Schema::createIfNotExists(MockEntity::class, $this->schemaOptions));
        self::assertTrue(Schema::exists('mocks', $this->dataSource));
        self::assertTrue(Schema::hasColumns('mocks', ['id', 'name', 'description', 'color_type'], $this->dataSource));
    }

    public function testInfoReturnsCreateTableStatement(): void
    {
        $info = Schema::info(MockEntity::class, $this->schemaOptions);

        self::assertNotNull($info);
        self::assertStringContainsString('CREATE TABLE `mocks`', $info->ddlStatement);
        self::assertArrayHasKey('id', $info->tableFields);
    }

    public function testRenameChangesTableName(): void
    {
        self::assertTrue(Schema::exists('mocks', $this->dataSource));

        try {
            self::assertTrue(Schema::rename('mocks', 'socks', $this->schemaOptions));
            self::assertTrue(Schema::exists('socks', $this->dataSource));
            self::assertFalse(Schema::exists('mocks', $this->dataSource));
        } finally {
            $this->dataSource->getClient()->exec("DROP TABLE IF EXISTS `{$this->databaseName}`.`socks`");
            Schema::createIfNotExists(MockEntity::class, $this->schemaOptions);
        }
    }

    public function testAlterAddsEmailColumnFromAlteredEntityShape(): void
    {
        self::assertTrue(Schema::alter(AlteredMockEntity::class, $this->schemaOptions));
        self::assertTrue(Schema::hasColumns('mocks', ['id', 'name', 'email'], $this->dataSource));
        self::assertFalse(Schema::hasColumns('mocks', ['description', 'color_type'], $this->dataSource));

        $statement = $this->dataSource->getClient()->prepare(
            'INSERT INTO `mocks` (`name`, `email`) VALUES (:name, :email)'
        );
        $statement->execute([
            'name' => 'Altered mock row',
            'email' => 'altered@example.com',
        ]);

        $row = $this->dataSource->getClient()->query('SELECT `name`, `email` FROM `mocks` ORDER BY `id` DESC LIMIT 1')->fetch();

        self::assertSame('Altered mock row', $row['name']);
        self::assertSame('altered@example.com', $row['email']);
    }

    public function testHasColumnsHandlesValidInvalidAndEmptyLists(): void
    {
        self::assertTrue(Schema::hasColumns('mocks', ['name', 'id'], $this->dataSource));
        self::assertFalse(Schema::hasColumns('mocks', ['this_column_does_not_exist', 'neither_does_this'], $this->dataSource));
        self::assertFalse(Schema::hasColumns('mocks', ['name', 'id', 'this_column_does_not_exist'], $this->dataSource));
        self::assertFalse(Schema::hasColumns('mocks', ['', null], $this->dataSource));
        self::assertFalse(Schema::hasColumns('mocks', [], $this->dataSource));
    }

    public function testTruncateRemovesPersistedRows(): void
    {
        $entity = new MockEntity();
        $entity->name = 'schema truncate one';
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
