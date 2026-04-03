<?php

namespace Tests\Unit;

use Assegai\Orm\DataSource\DBFactory;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use PDO;
use Tests\Support\UnitTester;

class ConnectionConfigCest
{
  public function includesConfiguredCharsetInMySqlDsn(UnitTester $I): void
  {
    $dsn = DBFactory::buildMySqlDsn('localhost', 3306, 'assegai', SQLCharacterSet::UTF8MB4);

    $I->assertSame('mysql:host=localhost;port=3306;dbname=assegai;charset=utf8mb4', $dsn);
  }

  public function appliesSafeDefaultMysqlPdoAttributes(UnitTester $I): void
  {
    $attributes = DBFactory::getDefaultPdoAttributes(SQLDialect::MYSQL);

    $I->assertSame(PDO::ERRMODE_EXCEPTION, $attributes[PDO::ATTR_ERRMODE]);
    $I->assertSame(PDO::FETCH_ASSOC, $attributes[PDO::ATTR_DEFAULT_FETCH_MODE]);
    $I->assertFalse($attributes[PDO::ATTR_STRINGIFY_FETCHES]);
    $I->assertFalse($attributes[PDO::ATTR_EMULATE_PREPARES]);
  }

  public function readsCharSetFromOptionsArrays(UnitTester $I): void
  {
    $options = DataSourceOptions::fromArray([
      'database' => 'assegai',
      'type' => DataSourceType::MYSQL,
      'charset' => 'utf8mb4',
    ]);

    $I->assertSame(SQLCharacterSet::UTF8MB4, $options->charSet);
  }
}
