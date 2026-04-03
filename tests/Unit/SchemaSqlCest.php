<?php

namespace Tests\Unit;

use Assegai\Orm\DataSource\Schema;
use Assegai\Orm\DataSource\SchemaOptions;
use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Enumerations\SchemaEngineType;
use ReflectionMethod;
use Tests\Support\UnitTester;
use Unit\mocks\MockEntity;

require_once __DIR__ . '/mocks/MockColorType.php';
require_once __DIR__ . '/mocks/MockEntity.php';

class SchemaSqlCest
{
  public function createIfNotExistsDdlIncludesGuardAndConfiguredMysqlOptions(UnitTester $I): void
  {
    $method = new ReflectionMethod(Schema::class, 'getDDLStatementFromEntity');
    $method->setAccessible(true);

    $sql = $method->invoke(
      null,
      MockEntity::class,
      new SchemaOptions(
        dbName: 'assegai_test_db',
        dialect: SQLDialect::MYSQL,
        checkIfExists: true,
        characterSet: SQLCharacterSet::LATIN1,
        engine: SchemaEngineType::MY_ISAM,
      )
    );

    $I->assertStringContainsString('CREATE TABLE IF NOT EXISTS `assegai_test_db`.`mocks`', $sql);
    $I->assertStringContainsString('ENGINE=MyISAM', $sql);
    $I->assertStringContainsString('DEFAULT CHARSET=latin1', $sql);
    $I->assertStringContainsString('COLLATE=latin1_swedish_ci', $sql);
  }
}
