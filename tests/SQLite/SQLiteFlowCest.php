<?php

namespace Tests\SQLite;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\Schema;
use Assegai\Orm\DataSource\SchemaOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Management\EntityManager;
use Tests\SQLite\Fixtures\UuidPrimaryMockEntity;
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
    require_once __DIR__ . '/Fixtures/UuidPrimaryMockEntity.php';

    $this->dbPath = dirname(__DIR__) . '/_output/sqlite-flow-' . uniqid('', true) . '.sqlite';
    @unlink($this->dbPath);
    $this->schemaOptions = new SchemaOptions(
      dbName: $this->dbPath,
      dialect: SQLDialect::SQLITE,
      checkIfExists: true,
    );

    $this->dataSource = new DataSource(new DataSourceOptions(
      entities: [MockEntity::class, UuidPrimaryMockEntity::class],
      name: $this->dbPath,
      type: DataSourceType::SQLITE,
    ));

    Schema::dropIfExists(MockEntity::class, $this->schemaOptions);
    Schema::createIfNotExists(MockEntity::class, $this->schemaOptions);

    $this->manager = $this->dataSource->manager;
  }

  public function _after(UnitTester $I): void
  {
    unset($this->manager, $this->dataSource);
    @unlink($this->dbPath);
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

    $otherEntity = new MockEntity();
    $otherEntity->name = 'sqlite test other';
    $otherEntity->description = 'Inserted to change SQLite last insert id';
    $otherEntity->colorType = MockColorType::RED;

    $otherInsertResult = $this->manager->insert(MockEntity::class, $otherEntity);
    $I->assertTrue($otherInsertResult->isOk());

    $upsertResult = $this->manager->upsert(MockEntity::class, $entity, ['id']);
    $I->assertTrue($upsertResult->isOk());
    $I->assertSame((int)$entityId, $entity->id);
    $I->assertSame((int)$entityId, $upsertResult->identifiers?->id);
    $I->assertSame((int)$entityId, $upsertResult->generatedMaps?->id);

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

  public function testSQLiteUpsertSupportsEntitiesWhosePrimaryKeyIsNotNamedId(UnitTester $I): void
  {
    Schema::dropIfExists(UuidPrimaryMockEntity::class, $this->schemaOptions);
    Schema::createIfNotExists(UuidPrimaryMockEntity::class, $this->schemaOptions);

    $entity = new UuidPrimaryMockEntity();
    $entity->uuid = 'uuid-flow-001';
    $entity->name = 'sqlite custom primary key';
    $entity->description = 'Inserted through custom primary key flow';

    $insertResult = $this->manager->insert(UuidPrimaryMockEntity::class, $entity);
    $I->assertTrue($insertResult->isOk());

    $entity->description = 'Updated through custom primary key upsert';

    $upsertResult = $this->manager->upsert(UuidPrimaryMockEntity::class, $entity, ['uuid']);

    $I->assertTrue($upsertResult->isOk());
    $I->assertSame('uuid-flow-001', $entity->uuid);
    $I->assertSame('uuid-flow-001', $upsertResult->identifiers?->uuid);
    $I->assertSame('uuid-flow-001', $upsertResult->generatedMaps?->uuid);

    $statement = $this->dataSource->getClient()->prepare(
      'SELECT `uuid`, `description` FROM `uuid_mocks` WHERE `uuid` = :uuid'
    );
    $statement->execute(['uuid' => $entity->uuid]);
    $row = $statement->fetch();

    $I->assertSame('uuid-flow-001', $row['uuid']);
    $I->assertSame('Updated through custom primary key upsert', $row['description']);
  }
}
