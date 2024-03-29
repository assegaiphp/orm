<?php


namespace Tests\Unit;

use Assegai\Core\Config;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\Schema;
use Assegai\Orm\DataSource\SchemaOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\DataSourceConnectionException;
use Assegai\Orm\Exceptions\GeneralSQLQueryException;
use Assegai\Orm\Exceptions\NotImplementedException;
use Assegai\Orm\Exceptions\ORMException;
use PDO;
use ReflectionException;
use Tests\Support\UnitTester;
use TypeError;
use Unit\mocks\AlteredMockEntity;
use Unit\mocks\MockEntity;

class SchemaCest
{
  protected ?object $entity = null;
  protected int $totalEntityProperties = 0;
  protected ?SchemaOptions $options = null;
  protected ?DataSource $dataSource = null;
  protected const DATASOURCE_NAME = 'assegai_test_db';
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

    $dbConfig = Config::get('databases')['mysql'][self::DATASOURCE_NAME];
    $databaseType = DataSourceType::MYSQL;

    $this->entity = new MockEntity();
    $this->totalEntityProperties = count(get_object_vars($this->entity));
    $this->options = new SchemaOptions(dbName: self::DATASOURCE_NAME,dialect: SQLDialect::MYSQL);
    $this->dataSource = new DataSource(new DataSourceOptions(
      entities: [],
      name: self::DATASOURCE_NAME,
      type: $databaseType,
      host: $dbConfig['host'],
      port: $dbConfig['port'],
      username: $dbConfig['user'],
      password: $dbConfig['password'],
    ));
    $dsn = 'mysql:host=' . $dbConfig['host'] . ';port=' . $dbConfig['port'] . ';dbname' . self::DATASOURCE_NAME;
    $connection = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);

    $dbName = self::DATASOURCE_NAME;
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

  public function _after(UnitTester $I): void
  {

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
      dbName: self::DATASOURCE_NAME
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

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClassNotFoundException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function testTheInfoMethod(UnitTester $I): void
  {
    $creationResult = Schema::create(MockEntity::class, $this->options);
    $I->assertTrue($creationResult);

    $infoResult = Schema::info(MockEntity::class, $this->options);
    $I->assertCount($this->totalEntityProperties, $infoResult->tableFields);
    $I->assertStringStartsWith("CREATE TABLE `mocks`", $infoResult->ddlStatement);
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClassNotFoundException
   * @throws ORMException
   * @throws ReflectionException
   * @throws DataSourceConnectionException
   */
  public function testTheTruncateMethod(UnitTester $I): void
  {
    $creationResult = Schema::create(MockEntity::class, $this->options);
    $I->assertTrue($creationResult);

    $I->haveInDatabase('mocks', ['name' => 'Shaka Zulu']);
    $I->haveInDatabase('mocks', ['name' => 'Zwengendaba']);
    $I->seeNumRecords(2, 'mocks');

    $truncateResult = Schema::truncate(MockEntity::class, $this->options);
    $I->assertTrue($truncateResult);
    $I->seeNumRecords(0, 'mocks');
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClassNotFoundException
   * @throws NotImplementedException
   * @throws ORMException
   */
  public function testTheDropMethod(UnitTester $I): void
  {
    $creationResult = Schema::create(MockEntity::class, $this->options);
    $I->assertTrue($creationResult);

    $dropResult = Schema::drop(MockEntity::class, $this->options);
    $I->assertTrue($dropResult);
  }

  /** @noinspection SpellCheckingInspection */
  /**
   * @param UnitTester $I
   * @return void
   * @throws ClassNotFoundException
   * @throws ORMException
   */
  public function testTheDropifexists(UnitTester $I): void
  {
    $creationResult = Schema::create(MockEntity::class, $this->options);
    $I->assertTrue($creationResult);

    $dropResult = Schema::dropIfExists(MockEntity::class, $this->options);
    $I->assertTrue($dropResult);
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws GeneralSQLQueryException
   */
  public function testTheExistsMethod(UnitTester $I): void
  {
    $validTableName = '__assegai_schema_migrations';
    $invalidTableName = 'no_existent_table';
    $noTableName = '';
    $nullTableName = null;

    $validTableExists = Schema::exists($validTableName, $this->dataSource);
    $I->assertTrue($validTableExists);

    $invalidTableDoesNotExist = Schema::exists($invalidTableName, $this->dataSource);
    $I->assertFalse($invalidTableDoesNotExist);

    $blankTableDoesNotExist = Schema::exists($noTableName, $this->dataSource);
    $I->assertFalse($blankTableDoesNotExist);

    try
    {
      Schema::exists($nullTableName, $this->dataSource);
    }
    catch (TypeError $error)
    {
      $I->assertStringContainsString('must be of type string, null given', $error->getMessage());
    }
  }

  /** @noinspection SpellCheckingInspection */
  /**
   * @param UnitTester $I
   * @return void
   * @throws ORMException
   */
  public function testTheHascolumnsMethod(UnitTester $I): void
  {
    $creationResult = Schema::create(MockEntity::class, $this->options);
    $I->assertTrue($creationResult);
    $tableName = 'mocks';

    $validColumnNames = ['name', 'id'];
    $validColumnNamesExist = Schema::hasColumns($tableName, $validColumnNames, $this->dataSource);
    $I->assertTrue($validColumnNamesExist);

    $nonExistentColumnNames = ['this_column_does_not_exist', 'neither_does_this'];
    $nonExistentColumnNamesExist = Schema::hasColumns($tableName, $nonExistentColumnNames, $this->dataSource);
    $I->assertFalse($nonExistentColumnNamesExist);

    $listOfValidAndInvalidColumnNames = array_merge($validColumnNames, $nonExistentColumnNames);
    $mixedListOfColumnNamesExist = Schema::hasColumns($tableName, $listOfValidAndInvalidColumnNames, $this->dataSource);
    $I->assertFalse($mixedListOfColumnNamesExist);

    $emptyColumnName = ['', null];
    $nonExistentColumnNamesExist = Schema::hasColumns($tableName, $emptyColumnName, $this->dataSource);
    $I->assertFalse($nonExistentColumnNamesExist);

    $emptyListOfColumnNames = [];
    $emptyListOfColumnNamesExist = Schema::hasColumns($tableName, $emptyListOfColumnNames, $this->dataSource);
    $I->assertFalse($emptyListOfColumnNamesExist);
  }
}
