<?php


namespace Tests\Unit;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\ORM\Exceptions\DataSourceException;
use Assegai\Orm\Exceptions\NotImplementedException;
use Assegai\Orm\Management\EntityManager;
use stdClass;
use Tests\Support\UnitTester;

class EntityManagerCest
{
  protected ?DataSourceOptions $dataSourceOptions = null;
  protected ?DataSource $dataSource = null;
  protected ?EntityManager $entityManager = null;

  /**
   * @param UnitTester $I
   * @return void
   * @throws DataSourceException
   */
  public function _before(UnitTester $I): void
  {
    $config = require(__DIR__ . '/config/default.php');
    $databaseConfig = $config['databases']['mysql'];

    $this->dataSourceOptions = new DataSourceOptions(
      entities: [],
      database: $databaseConfig['name'] ?? '',
      type: DataSourceType::MARIADB,
      host: $databaseConfig['host'] ?? 'localhost',
      port: $databaseConfig['port'] ?? 3306,
      username: $databaseConfig['user'] ?? 'root',
      password: $databaseConfig['pass'] ?? '',
      charSet: $databaseConfig['charSet'] ?? SQLCharacterSet::UTF8MB4
    );
    $this->dataSource = new DataSource($this->dataSourceOptions);

    $this->entityManager = new EntityManager(connection: $this->dataSource);
  }

  // tests
  public function testTheQueryMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  public function testTheSaveMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheValidateentitynameMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  public function testTheCreateMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  public function testTheMergeMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  public function testThePreloadMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  public function testTheInsertMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  public function testTheUpdateMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  public function testTheUpsertMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheSoftremoveMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheDeleteMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheRestoreMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheCountMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheFindMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheFindbyMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheFindandcountMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheFindandcountbyMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheFindoneMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheUseconvertersMethod(UnitTester $I): void
  {
    $invalidConverter = new stdClass();
    $this->entityManager->useConverters([$invalidConverter]);
  }
}
