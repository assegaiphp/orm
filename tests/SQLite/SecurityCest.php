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

class SecurityCest
{
  private string $dbPath;
  private DataSource $dataSource;
  private EntityManager $manager;

  public function _before(UnitTester $I): void
  {
    require_once dirname(__DIR__) . '/Unit/mocks/MockColorType.php';
    require_once dirname(__DIR__) . '/Unit/mocks/MockEntity.php';

    $this->dbPath = dirname(__DIR__) . '/_output/sqlite-security-' . uniqid('', true) . '.sqlite';
    @unlink($this->dbPath);

    $this->dataSource = new DataSource(new DataSourceOptions(
      entities: [MockEntity::class],
      name: $this->dbPath,
      type: DataSourceType::SQLITE,
    ));

    $schemaOptions = new SchemaOptions(
      dbName: $this->dbPath,
      dialect: SQLDialect::SQLITE,
      checkIfExists: true,
    );

    Schema::dropIfExists(MockEntity::class, $schemaOptions);
    Schema::createIfNotExists(MockEntity::class, $schemaOptions);

    $this->manager = $this->dataSource->manager;
  }

  public function _after(UnitTester $I): void
  {
    unset($this->manager, $this->dataSource);
    @unlink($this->dbPath);
  }

  public function safelyHandlesQuotedValuesAcrossInsertUpdateDelete(UnitTester $I): void
  {
    $entity = new MockEntity();
    $entity->name = "O'Reilly";
    $entity->description = "Robert'); DROP TABLE mocks; --";
    $entity->colorType = MockColorType::BLUE;

    $insertResult = $this->manager->insert(MockEntity::class, $entity);

    $I->assertTrue($insertResult->isOk());
    $entity->id = $insertResult->generatedMaps?->id ?? $insertResult->identifiers?->id;

    $insertedRowStatement = $this->dataSource->getClient()->prepare(
      'SELECT `name`, `description` FROM `mocks` WHERE `id` = ?'
    );
    $insertedRowStatement->execute([(int)$entity->id]);
    $insertedRow = $insertedRowStatement->fetch();

    $I->assertSame("O'Reilly", $insertedRow['name']);
    $I->assertSame("Robert'); DROP TABLE mocks; --", $insertedRow['description']);

    $count = (int)$this->dataSource->getClient()->query('SELECT COUNT(*) FROM `mocks`')->fetchColumn();
    $I->assertSame(1, $count);

    $partial = (object)[
      'description' => "Updated 'quote' value",
    ];

    $updateResult = $this->manager->update(
      MockEntity::class,
      $partial,
      ['name' => $insertedRow['name']]
    );

    $I->assertTrue($updateResult->isOk());

    $statement = $this->dataSource->getClient()->prepare(
      'SELECT `description` FROM `mocks` WHERE `id` = ?'
    );
    $statement->execute([(int)$entity->id]);
    $updatedRow = $statement->fetch();

    $I->assertSame("Updated 'quote' value", $updatedRow['description']);

    $deleteResult = $this->manager->delete(MockEntity::class, ['name' => "O'Reilly"]);
    $I->assertTrue($deleteResult->isOk());

    $remaining = (int)$this->dataSource->getClient()->query('SELECT COUNT(*) FROM `mocks`')->fetchColumn();
    $I->assertSame(0, $remaining);
  }

  public function supportsParameterizedRawQueriesAndQuotedSqliteUpserts(UnitTester $I): void
  {
    $entity = new MockEntity();
    $entity->name = "API's favorite";
    $entity->description = "Inserted with 'quotes'";
    $entity->colorType = MockColorType::RED;

    $insertResult = $this->manager->insert(MockEntity::class, $entity);
    $I->assertTrue($insertResult->isOk());

    $entity->id = $insertResult->generatedMaps?->id ?? $insertResult->identifiers?->id;

    $insertedRowStatement = $this->dataSource->getClient()->prepare(
      'SELECT `name` FROM `mocks` WHERE `id` = ?'
    );
    $insertedRowStatement->execute([(int)$entity->id]);
    $insertedRow = $insertedRowStatement->fetch();
    $I->assertSame("API's favorite", $insertedRow['name']);

    $statement = $this->manager->query(
      'SELECT COUNT(*) AS total FROM `mocks` WHERE `name` = ?',
      [$insertedRow['name']]
    );

    $row = $statement?->fetch();
    $I->assertSame(1, (int)($row['total'] ?? 0));

    $entity->description = "Upserted 'quoted' value";
    $upsertResult = $this->manager->upsert(MockEntity::class, $entity, ['id']);

    $I->assertTrue($upsertResult->isOk());

    $updatedRow = $this->dataSource->getClient()
      ->query('SELECT `description` FROM `mocks` WHERE `id` = ' . (int)$entity->id)
      ->fetch();

    $I->assertSame("Upserted 'quoted' value", $updatedRow['description']);
  }

  public function supportsParameterlessRawQueries(UnitTester $I): void
  {
    $tableName = 'raw_query_probe';

    $createStatement = $this->manager->query(
      "CREATE TABLE IF NOT EXISTS `$tableName` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` TEXT NOT NULL)"
    );
    $I->assertNotFalse($createStatement);

    $insertStatement = $this->manager->query(
      "INSERT INTO `$tableName` (`name`) VALUES ('prepared path')"
    );
    $I->assertNotFalse($insertStatement);

    $countStatement = $this->manager->query("SELECT COUNT(*) AS total FROM `$tableName`");
    $row = $countStatement?->fetch();

    $I->assertSame(1, (int)($row['total'] ?? 0));
  }

}
