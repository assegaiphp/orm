<?php


namespace Tests\Unit;

use Assegai\Core\Config;
use Assegai\Orm\DataSource\Schema;
use Assegai\Orm\DataSource\SchemaOptions;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\NotImplementedException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Management\EntityInspector;
use PDO;
use ReflectionException;
use Tests\Support\UnitTester;
use Unit\mocks\AlteredMockEntity;
use Unit\mocks\MockEntity;

class SchemaCest
{
  protected ?object $entity = null;
  protected ?SchemaOptions $options = null;
  protected const DB_NAME = 'assegai_test_db';
  protected const TABLE_NAME = 'mocks';

  /**
   * @param UnitTester $I
   * @return void
   * @throws ORMException
   * @noinspection SqlResolve
   */
  public function _before(UnitTester $I): void
  {
    spl_autoload_register(function ($classname) {
      $classname = str_replace('Unit\\mocks\\', '', $classname);

      require __DIR__ . "/mocks/$classname.php";
    });

    $config = require(__DIR__ . '/config/default.php');
    $dbConfig = Config::get('databases')['mysql'][self::DB_NAME];
    $dbConfig['name'] = self::DB_NAME;

    $this->entity = new MockEntity();
    $this->options = new SchemaOptions(dbName: $dbConfig['name'],dialect: SQLDialect::MYSQL);
    $dsn = 'mysql:host=' . $dbConfig['host'] . ';port=' . $dbConfig['port'] . ';dbname' . $dbConfig['name'];
    $connection = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);

    $dbName = self::DB_NAME;
    $tableName = self::TABLE_NAME;
    $statement = $connection->exec("DROP TABLE IF EXISTS `$dbName`.`$tableName`");

    if (false === $statement)
    {
      throw new ORMException("Failed to drop table '$tableName'");
    }

    $statement = $connection->exec("DROP TABLE IF EXISTS `$dbName`.`socks`");
    if (false === $statement)
    {
      throw new ORMException("Failed to drop table '$tableName'");
    }
  }

  // tests

  /**
   * @throws ORMException
   * @throws ClassNotFoundException
   */
  public function testTheCreateMethod(UnitTester $I): void
  {
    $creationResult = Schema::create(MockEntity::class, $this->options);

    $I->assertTrue($creationResult);
    $I->seeNumRecords(0, self::TABLE_NAME);
  }

  /**
   * @throws ORMException
   * @noinspection SpellCheckingInspection
   */
  public function testTheCreateifnotexistsMethod(UnitTester $I): void
  {
    $creationResult = Schema::createIfNotExists(MockEntity::class, $this->options);

    $I->assertTrue($creationResult);
    $I->seeNumRecords(0, self::TABLE_NAME);
  }

  /**
   * @throws ORMException
   */
  public function testTheRenameMethod(UnitTester $I): void
  {
    $creationResult = Schema::createIfNotExists(MockEntity::class, $this->options);
    $I->assertTrue($creationResult);

    $renameResult = Schema::rename(self::TABLE_NAME, 'socks', new SchemaOptions(
      dbName: self::DB_NAME
    ));
    $I->assertTrue($renameResult);
  }

  /**
   * @throws ClassNotFoundException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function testTheAlterMethod(UnitTester $I): void
  {
    $creationResult = Schema::create(MockEntity::class, $this->options);
    $I->assertTrue($creationResult);

    $email = 'hello@assegaiphp.com';
    $alterResult = Schema::alter(AlteredMockEntity::class, $this->options);
    $I->assertTrue($alterResult);
    $I->haveInDatabase(self::TABLE_NAME, ['email' => $email]);
    $I->seeNumRecords(1, self::TABLE_NAME);
    $I->seeInDatabase(self::TABLE_NAME, ['email' => $email]);
  }

  public function testTheInfoMethod(UnitTester $I): void
  {
    $infoResult = Schema::info(MockEntity::class, $this->options);
  }

  public function testTheTruncateMethod(UnitTester $I): void
  {
    // TODO: Implement the testTheTruncateMethod() method.
    throw new NotImplementedException(__METHOD__);
  }

  public function testTheDropMethod(UnitTester $I): void
  {
    // TODO: Implement the testTheDropMethod() method.
    throw new NotImplementedException(__METHOD__);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheDropifexists(UnitTester $I): void
  {
    // TODO: Implement the testTheDropifexists() method.
    throw new NotImplementedException(__METHOD__);
  }

  public function testTheExistsMethod(UnitTester $I): void
  {
    // TODO: Implement the testTheExistsMethod() method.
    throw new NotImplementedException(__METHOD__);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheHascolumnsMethod(UnitTester $I): void
  {
    // TODO: Implement the testTheHascolumnsMethod() method.
    throw new NotImplementedException(__METHOD__);
  }
}
