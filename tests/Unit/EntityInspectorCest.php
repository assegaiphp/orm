<?php


namespace Tests\Unit;

use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Management\EntityInspector;
use Exception;
use Tests\Support\UnitTester;
use Unit\mocks\MockEntity;
use Unit\mocks\NotAMockEntity;

class EntityInspectorCest
{
  protected ?EntityInspector $inspector = null;
  protected ?object $entity = null;
  protected ?object $invalidEntity = null;

  protected const TABLE_NAME = 'mocks';
  protected const DATABASE_NAME = 'assegaiphp_test_db';

  public function _before(UnitTester $I)
  {
    spl_autoload_register(function ($classname) {
      $classname = str_replace('Unit\\mocks\\', '', $classname);
      require_once __DIR__ . "/mocks/$classname.php";
    });

    $this->inspector = EntityInspector::getInstance();
    $this->entity = new MockEntity();
    $this->invalidEntity = new NotAMockEntity();
  }

  // tests
  public function tryToGetAnEntityInspectorInstance(UnitTester $I): void
  {
    $instance = EntityInspector::getInstance();
    $I->assertNotNull($instance);
  }

  public function tryToValidateEntityNames(UnitTester $I): void
  {
    $validEntityName = MockEntity::class;
    $invalidEntityClassName = NotAMockEntity::class;
    EntityInspector::validateEntityName($validEntityName);

    try
    {
      EntityInspector::validateEntityName($invalidEntityClassName);
    }
    catch (Exception $exception)
    {
    }
  }

  public function tryToGetMetaDataFromAnEntityInstance(UnitTester $I): void
  {
    $metaData = EntityInspector::getMetaData($this->entity);
    $I->assertEquals('mocks', $metaData->table);
    $I->assertEquals('assegaiphp_test_db', $metaData->database);
  }

  public function tryToGetColumnPropertiesFromAnEntityInstance(UnitTester $I): void
  {
    $columnProperties = EntityInspector::getInstance()->getColumns($this->entity);
    $I->assertArrayHasKey('id', $columnProperties);
    $I->assertArrayHasKey('createdAt', $columnProperties);
    $I->assertArrayNotHasKey('rank', $columnProperties);

    $columnProperties = $this->inspector->getColumns($this->entity, ['id']);
    $I->assertArrayNotHasKey('id', $columnProperties);

    # TODO: Test relations option

    # TODO: Test relationProperties option
  }

  public function tryToGetPropertyValuesFromAnEntityInstance(UnitTester $I): void
  {
    $expectedName = 'Shaka';
    $this->entity->name = $expectedName;

    $entityValues = $this->inspector->getValues($this->entity);

    $I->assertTrue(in_array($expectedName, $entityValues));
    $I->assertFalse(in_array('Caesar', $entityValues));
  }

  public function tryToGetTheTableNameOfAnEntityInstance(UnitTester $I): void
  {
    $tableName = $this->inspector->getTableName($this->entity);
    $I->assertEquals(self::TABLE_NAME, $tableName);

    try
    {
      $tableName = $this->inspector->getTableName($this->invalidEntity);
    }
    catch (Exception $exception)
    {
      $I->assertInstanceOf(ORMException::class, $exception);
    }
  }
}
