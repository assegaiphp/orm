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
use Unit\mocks\AlteredMockEntity;
use Unit\mocks\MockColorType;
use Unit\mocks\MockEntity;

class SchemaAlterCest
{
  private string $dbPath;
  private DataSource $dataSource;
  private SchemaOptions $schemaOptions;
  private EntityManager $manager;

  public function _before(UnitTester $I): void
  {
    require_once dirname(__DIR__) . '/Unit/mocks/MockColorType.php';
    require_once dirname(__DIR__) . '/Unit/mocks/MockEntity.php';
    require_once dirname(__DIR__) . '/Unit/mocks/AlteredMockEntity.php';

    $this->dbPath = dirname(__DIR__) . '/_output/sqlite-schema-alter-' . uniqid('', true) . '.sqlite';
    @unlink($this->dbPath);
    $this->schemaOptions = new SchemaOptions(
      dbName: $this->dbPath,
      dialect: SQLDialect::SQLITE,
      checkIfExists: true,
    );

    $this->dataSource = new DataSource(new DataSourceOptions(
      entities: [MockEntity::class, AlteredMockEntity::class],
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

  public function testSQLiteAlterRebuildsTheTableAndPreservesSharedData(UnitTester $I): void
  {
    $entity = new MockEntity();
    $entity->name = 'sqlite before alter';
    $entity->description = 'This column should disappear after alter';
    $entity->colorType = MockColorType::GREEN;

    $insertResult = $this->manager->insert(MockEntity::class, $entity);
    $I->assertTrue($insertResult->isOk());

    $entityId = (int)($insertResult->generatedMaps?->id ?? $insertResult->identifiers?->id);
    $alterResult = Schema::alter(AlteredMockEntity::class, $this->schemaOptions);

    $I->assertTrue($alterResult);
    $I->assertTrue(Schema::hasColumns('mocks', ['id', 'name', 'email', 'created_at', 'updated_at', 'deleted_at'], $this->dataSource));
    $I->assertFalse(Schema::hasColumns('mocks', ['description'], $this->dataSource));
    $I->assertFalse(Schema::hasColumns('mocks', ['color_type'], $this->dataSource));

    $statement = $this->dataSource->getClient()->prepare('SELECT `id`, `name`, `email` FROM `mocks` WHERE `id` = :id');
    $statement->execute(['id' => $entityId]);
    $row = $statement->fetch();

    $I->assertSame($entityId, (int)$row['id']);
    $I->assertSame('sqlite before alter', $row['name']);
    $I->assertNull($row['email']);

    $insertAfterAlter = $this->dataSource->getClient()->prepare('INSERT INTO `mocks` (`name`, `email`) VALUES (:name, :email)');
    $I->assertTrue($insertAfterAlter->execute([
      'name' => 'sqlite after alter',
      'email' => 'after@assegaiphp.com',
    ]));

    $count = (int)$this->dataSource->getClient()->query('SELECT COUNT(*) FROM `mocks`')->fetchColumn();
    $I->assertSame(2, $count);

    $info = Schema::info(AlteredMockEntity::class, $this->schemaOptions);
    $I->assertNotNull($info);
    $I->assertStringContainsString('email', strtolower($info->ddlStatement));
  }
}
