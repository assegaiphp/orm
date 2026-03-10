<?php

namespace Tests\SQLite;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\Schema;
use Assegai\Orm\DataSource\SchemaOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Management\EntityManager;
use Tests\Support\UnitTester;
use Unit\mocks\MockColorType;
use Unit\mocks\MockEntity;

class SQLiteFlowCest
{
  private string $dbPath;
  private DataSource $dataSource;
  private SchemaOptions $schemaOptions;
  private EntityManager $manager;

  public function _before(UnitTester $I): void
  {
    require_once dirname(__DIR__) . '/Unit/mocks/MockColorType.php';
    require_once dirname(__DIR__) . '/Unit/mocks/MockEntity.php';

    $this->dbPath = dirname(__DIR__) . '/Support/Data/sqlite-flow.sqlite';
    $this->schemaOptions = new SchemaOptions(
      dbName: $this->dbPath,
      dialect: SQLDialect::SQLITE,
      checkIfExists: true,
    );

    $this->dataSource = new DataSource(new DataSourceOptions(
      entities: [MockEntity::class],
      name: $this->dbPath,
      type: DataSourceType::SQLITE,
    ));

    Schema::dropIfExists(MockEntity::class, $this->schemaOptions);
    Schema::createIfNotExists(MockEntity::class, $this->schemaOptions);

    $this->manager = $this->dataSource->manager;
  }

  public function testTheSQLiteFlow(UnitTester $I): void
  {
    $I->assertTrue(Schema::exists('mocks', $this->dataSource));
    $I->assertTrue(Schema::hasColumns('mocks', ['id', 'name', 'description', 'color_type'], $this->dataSource));

    $entity = new MockEntity();
    $entity->name = 'sqlite test';
    $entity->description = 'Created through the SQLite flow';
    $entity->colorType = MockColorType::BLUE;

    $insertResult = $this->manager->insert(MockEntity::class, $entity);
    $I->assertTrue($insertResult->isOk());

    $entityId = $insertResult->generatedMaps?->id ?? $insertResult->identifiers?->id;
    $row = $this->dataSource->getClient()
      ->query("SELECT `name`, `description`, `color_type` FROM `mocks` WHERE `id` = $entityId")
      ->fetch();

    $I->assertSame('sqlite test', $row['name']);
    $I->assertSame('Created through the SQLite flow', $row['description']);
    $I->assertSame(MockColorType::BLUE->value, $row['color_type']);

    $entity->id = (int)$entityId;
    $entity->description = 'Updated through SQLite upsert';

    $upsertResult = $this->manager->upsert(MockEntity::class, $entity, ['id']);
    $I->assertTrue($upsertResult->isOk());

    $updatedRow = $this->dataSource->getClient()
      ->query("SELECT `description` FROM `mocks` WHERE `id` = $entityId")
      ->fetch();

    $I->assertSame('Updated through SQLite upsert', $updatedRow['description']);

    $info = Schema::info(MockEntity::class, $this->schemaOptions);
    $I->assertNotNull($info);
    $I->assertStringContainsString('CREATE TABLE', $info->ddlStatement);

    $truncateResult = Schema::truncate(MockEntity::class, $this->schemaOptions);
    $I->assertTrue($truncateResult);

    $count = (int)$this->dataSource->getClient()->query('SELECT COUNT(*) FROM `mocks`')->fetchColumn();
    $I->assertSame(0, $count);
  }
}
