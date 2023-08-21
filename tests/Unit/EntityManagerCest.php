<?php


namespace Tests\Unit;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\ORM\Exceptions\DataSourceException;
use Assegai\Orm\Exceptions\NotImplementedException;
use Assegai\Orm\Management\EntityManager;
use Codeception\Attribute\Skip;
use stdClass;
use Tests\Support\UnitTester;

class EntityManagerCest
{
  const DATA_SOURCE_NAME = 'assegai_test_db';
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
    $databaseType = DataSourceType::MYSQL;
    $databaseConfig = $config['databases'][$databaseType->value][self::DATA_SOURCE_NAME];

    $this->dataSourceOptions = new DataSourceOptions(
      entities: $databaseConfig['entities'] ?? [],
      name: self::DATA_SOURCE_NAME,
      type: $databaseType,
      host: $databaseConfig['host'] ?? 'localhost',
      port: $databaseConfig['port'] ?? 3306,
      username: $databaseConfig['user'] ?? $databaseConfig['username'] ?? 'root',
      password: $databaseConfig['pass'] ?? $databaseConfig['password'] ?? '',
      charSet: $databaseConfig['charSet'] ?? SQLCharacterSet::UTF8MB4
    );
    $this->dataSource = new DataSource($this->dataSourceOptions);

    $this->entityManager = new EntityManager(connection: $this->dataSource);
  }

  // tests
  #[Skip]
  public function testTheQueryMethod(UnitTester $I): void
  {
    // TODO: Implement testTheQueryMethod() method.
  }

  #[Skip]
  public function testTheSaveMethod(UnitTester $I): void
  {
    // TODO: Implement testTheSaveMethod() method.
  }

  /** @noinspection SpellCheckingInspection */
  #[Skip]
  public function testTheValidateentitynameMethod(UnitTester $I): void
  {
    // TODO: Implement testTheValidateentitynameMethod() method.
  }

  #[Skip]
  public function testTheCreateMethod(UnitTester $I): void
  {
    // TODO: Implement testTheCreateMethod() method.
  }

  #[Skip]
  public function testTheMergeMethod(UnitTester $I): void
  {
    // TODO: Implement testTheMergeMethod() method.
  }

  #[Skip]
  public function testThePreloadMethod(UnitTester $I): void
  {
    // TODO: Implement testThePreloadMethod() method.
  }

  #[Skip]
  public function testTheInsertMethod(UnitTester $I): void
  {
    // TODO: Implement testTheInsertMethod() method.
  }

  #[Skip]
  public function testTheUpdateMethod(UnitTester $I): void
  {
    // TODO: Implement testTheUpdateMethod() method.
  }

  #[Skip]
  public function testTheUpsertMethod(UnitTester $I): void
  {
    // TODO: Implement testTheUpsertMethod() method.
  }

  /** @noinspection SpellCheckingInspection */
  #[Skip]
  public function testTheSoftremoveMethod(UnitTester $I): void
  {
    // TODO: Implement testTheSoftremoveMethod() method.
  }

  /** @noinspection SpellCheckingInspection */
  #[Skip]
  public function testTheDeleteMethod(UnitTester $I): void
  {
    // TODO: Implement testTheDeleteMethod() method.
  }

  /** @noinspection SpellCheckingInspection */
  #[Skip]
  public function testTheRestoreMethod(UnitTester $I): void
  {
    // TODO: Implement testTheRestoreMethod() method.
  }

  /** @noinspection SpellCheckingInspection */
  #[Skip]
  public function testTheCountMethod(UnitTester $I): void
  {
    // TODO: Implement testTheCountMethod() method.
  }

  /** @noinspection SpellCheckingInspection */
  #[Skip]
  public function testTheFindMethod(UnitTester $I): void
  {
    // TODO: Implement testTheFindMethod() method.
  }

  /** @noinspection SpellCheckingInspection */
  #[Skip]
  public function testTheFindbyMethod(UnitTester $I): void
  {
    // TODO: Implement testTheFindbyMethod() method.
  }

  /** @noinspection SpellCheckingInspection */
  #[Skip]
  public function testTheFindandcountMethod(UnitTester $I): void
  {
    // TODO: Implement testTheFindandcountMethod() method.
  }

  /** @noinspection SpellCheckingInspection */
  #[Skip]
  public function testTheFindandcountbyMethod(UnitTester $I): void
  {
    // TODO: Implement testTheFindandcountbyMethod() method.
  }

  /** @noinspection SpellCheckingInspection */
  #[Skip]
  public function testTheFindoneMethod(UnitTester $I): void
  {
    // TODO: Implement testTheFindoneMethod() method.
  }

  /** @noinspection SpellCheckingInspection */
  #[Skip]
  public function testTheUseconvertersMethod(UnitTester $I): void
  {
    $invalidConverter = new stdClass();
    $this->entityManager->useConverters([$invalidConverter]);
  }
}
