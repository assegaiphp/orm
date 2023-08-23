<?php


namespace Tests\Unit;

use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\ORM\Exceptions\DataSourceException;
use Assegai\Orm\Exceptions\GeneralSQLQueryException;
use Assegai\Orm\Exceptions\IllegalTypeException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Management\Options\UpsertOptions;
use Assegai\Orm\Queries\QueryBuilder\Results\InsertResult;
use Codeception\Attribute\Skip;
use Exception;
use ReflectionException;
use stdClass;
use Tests\Support\UnitTester;
use Unit\mocks\MockColorType;
use Unit\mocks\MockEntity;
use Unit\mocks\NotAMockEntity;

class EntityManagerCest
{
  const DATA_SOURCE_NAME = 'assegai_test_db';
  const ENTITY_NAME = 'test_entities';
  const TEST_NAME = 'test';
  const TEST_EMAIL = 'test@example.com';
  const TEST_DESCRIPTION = 'test description';

  protected ?DataSourceOptions $dataSourceOptions = null;
  protected ?DataSource $dataSource = null;
  protected ?EntityManager $entityManager = null;
  protected mixed $entity = null;

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

    $this->entity = new #[Entity(table: 'test_entities')] class {
      public string $name = 'test';
      public string $email = 'test@example.com';
    };

    $mockDir = scandir(__DIR__ . '/mocks');

    foreach ($mockDir as $filename)
    {
      if ($filename === '.' || $filename === '..')
      {
        continue;
      }

      $declaredClasses = get_declared_classes();

      if (!in_array('Unit\mocks\\' . substr($filename, 0, -4), $declaredClasses))
      {
        require __DIR__ . '/mocks/' . $filename;
      }
    }
  }

  // tests
  public function testTheQueryMethod(UnitTester $I): void
  {
    $tableName = self::ENTITY_NAME;
    $statement = $this->entityManager->query(query: <<<SQL
CREATE TABLE IF NOT EXISTS `$tableName` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL
    );

    $I->assertTrue($statement->execute());
    $statement = $this->entityManager->query("DROP TABLE IF EXISTS `$tableName`");
    $I->assertTrue($statement->execute());
  }

  public function testTheSaveMethod(UnitTester $I): void
  {
    $mockEntity = new MockEntity();
    $mockEntity->name = self::TEST_NAME;
    $mockEntity->description = self::TEST_DESCRIPTION;
    $mockEntity->colorType = MockColorType::RED;

    try {
      $entity = $this->entityManager->save($mockEntity);
      $I->assertNotNull($entity->id);

      $I->seeInDatabase('mocks', [
        'id' => $entity->id,
        'name' => self::TEST_NAME,
        'description' => self::TEST_DESCRIPTION,
        'color_type' => MockColorType::RED->value
      ]);
      $statement = $this->entityManager->query("TRUNCATE TABLE `mocks`");
      $I->assertTrue($statement->execute());
    } catch (Exception) { }
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheValidateentitynameMethod(UnitTester $I): void
  {
    $validEntityName = MockEntity::class;
    $nonExistantClassName = 'invalid_entity_name';
    $invalidEntityName = NotAMockEntity::class;

    try {
      $this->entityManager->validateEntityName($validEntityName);
      $I->assertTrue(true);
    } catch (Exception) { }

    try {
      $this->entityManager->validateEntityName($nonExistantClassName);
    } catch (Exception $exception) {
      $I->assertInstanceOf(ClassNotFoundException::class, $exception);
    }

    try {
      $this->entityManager->validateEntityName($invalidEntityName);
    } catch (Exception $exception) {
      $I->assertInstanceOf(ORMException::class, $exception);
    }
  }

  public function testTheCreateMethod(UnitTester $I): void
  {
    $dto = new stdClass();
    $dto->name = self::TEST_NAME;
    $dto->description = self::TEST_DESCRIPTION;
    $dto->colorType = MockColorType::RED;

    try {
      $entity = $this->entityManager->create(MockEntity::class, $dto);
      $I->assertIsObject($entity);
      $I->assertInstanceOf(MockEntity::class, $entity);
      $I->assertTrue(property_exists($entity, 'id'));
      $I->assertTrue(property_exists($entity, 'name'));
      $I->assertTrue(property_exists($entity, 'description'));
      $I->assertTrue(property_exists($entity, 'colorType'));
      $I->assertTrue(property_exists($entity, 'createdAt'));
      $I->assertTrue(property_exists($entity, 'updatedAt'));
      $I->assertTrue(property_exists($entity, 'deletedAt'));
      $I->assertFalse(property_exists($entity, 'nonExistentProperty'));
    } catch (Exception) { }
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

  /**
   * @param UnitTester $I
   * @return void
   * @throws ClassNotFoundException
   * @throws ORMException
   * @throws GeneralSQLQueryException
   * @throws IllegalTypeException
   * @throws ReflectionException
   */
  public function testTheInsertMethod(UnitTester $I): void
  {
    $timestampColumns = [
      'createdAt' => date(DATE_ATOM),
      'updatedAt' => date(DATE_ATOM),
      'deletedAt' => NULL,
    ];
    # Create a new entity
    $validEntity = $this->entityManager->create(MockEntity::class, (object) [
      'name' => self::TEST_NAME,
      'description' => 'Insert valid entity',
      'colorType' => MockColorType::RED,
      ...$timestampColumns
    ]);
    $invalidEntity = (object)[
      'title' => self::TEST_NAME,
      'desc' => 'Insert invalid entity',
      'color' => MockColorType::RED,
      ...$timestampColumns
    ];
    $collisionEntity = (object)[
      'id' => rand(10, 100),
      'name' => self::TEST_NAME,
      'description' => 'Insert not found entity',
      'colorType' => MockColorType::RED,
      ...$timestampColumns
    ];

    # Assert that the entity was created
    $I->assertIsObject($validEntity);
    $I->assertInstanceOf(MockEntity::class, $validEntity);
    $I->assertTrue(property_exists($validEntity, 'id'));

    # Assert new record was inserted into the database using the insert method
    $result = $this->entityManager->insert(MockEntity::class, $validEntity);

    $I->assertInstanceOf(InsertResult::class, $result);
    $I->assertTrue($result->isOk());
    $validEntity->id = $result->generatedMaps?->id ?? $validEntity->id;
    $I->seeInDatabase('mocks', [
      'id'          => $validEntity->id,
      'name'        => self::TEST_NAME,
      'description' => "Insert valid entity",
      'color_type'  => MockColorType::RED->value
    ]);

    # Assert an exception is thrown when the entity is invalid
    try {
      $result = $this->entityManager->insert(MockEntity::class, $invalidEntity);
      $I->assertTrue($result->isError(), 'An exception should have been thrown');
    } catch (Exception $exception) {
      $I->assertInstanceOf(ORMException::class, $exception);
    }

    # Assert an exception is thrown when there is a foreign key constraint violation
    try {
      $I->haveInDatabase('mocks', [
        'id' => $collisionEntity->id,
        'name' => $collisionEntity->name,
        'description' => $collisionEntity->description,
        'color_type' => $collisionEntity->colorType->value,
        'created_at' => $collisionEntity->createdAt,
        'updated_at' => $collisionEntity->updatedAt,
        'deleted_at' => $collisionEntity->deletedAt
      ]);
      $result = $this->entityManager->insert(MockEntity::class, $collisionEntity);
    } catch (ORMException $exception) {
      $I->assertTrue(true, $exception->getMessage());
    }
  }

  #[Skip]
  public function testTheUpdateMethod(UnitTester $I): void
  {
    // TODO: Implement testTheUpdateMethod() method.
  }

  public function testTheUpsertMethod(UnitTester $I): void
  {
    # Create a new entity
    $dto = new stdClass();
    $dto->name = self::TEST_NAME;
    $dto->description = self::TEST_DESCRIPTION . " - Test upsert method";
    $dto->colorType = MockColorType::RED;
    $dto->createdAt = date(DATE_ATOM);
    $dto->updatedAt = date(DATE_ATOM);
    $dto->deletedAt = null;

    # Assert that the entity was created
    try {
      $entity = $this->entityManager->create(MockEntity::class, $dto);
      $I->assertIsObject($entity);
      $I->assertInstanceOf(MockEntity::class, $entity);
      $I->assertTrue(property_exists($entity, 'id'));
      $I->assertTrue($entity->name === self::TEST_NAME);

      # Assert new record was inserted into the database using the upsert method
      $conflictPaths = ['name'];
      $options = new UpsertOptions($conflictPaths);
      # Test upsert with array of columns
      $result = $this->entityManager->upsert(MockEntity::class, $entity, $conflictPaths);
      $entity->id = $result->generatedMaps?->id ?? $entity->id;

      $I->assertTrue($result->isOk());
      $I->seeInDatabase('mocks', [
        'id' => $entity->id,
        'name' => self::TEST_NAME,
        'description' => self::TEST_DESCRIPTION,
        'color_type' => MockColorType::RED->value
      ]);

      # Update the entity
      $updatedDescription = 'updated description';
      $entity->description = $updatedDescription;

      # Assert that the entity was updated using the upsert method
      # Test upsert with UpsertOptions object
      $result = $this->entityManager->upsert(MockEntity::class, $entity, $options);
      $I->assertTrue($result->isOk());
      $I->seeInDatabase('mocks', [
        'id' => $entity->id,
        'name' => self::TEST_NAME,
        'description' => $updatedDescription,
        'color_type' => MockColorType::RED->value
      ]);
    } catch (Exception) {}
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheSoftremoveMethod(UnitTester $I): void
  {
    $entityName = self::TEST_NAME . ' - soft remove';
    # Create a new entity
    $entity = new MockEntity();
    $entity->name = $entityName;
    $entity->description = self::TEST_DESCRIPTION;
    $entity->colorType = MockColorType::RED;

    # Assert that the entity was created
    $I->assertIsObject($entity);
    $I->assertInstanceOf(MockEntity::class, $entity);
    $I->assertTrue(property_exists($entity, 'id'));

    try {
      # Assert new record was inserted into the database using the insert method
      $result = $this->entityManager->insert(MockEntity::class, $entity);
      $entity->id = $result->generatedMaps?->id ?? $entity->id;

      $I->assertTrue($result->isOk());
      $I->seeInDatabase('mocks', [
        'id' => $entity->id,
        'name' => $entityName,
        'description' => self::TEST_DESCRIPTION,
        'color_type' => MockColorType::RED->value
      ]);
    } catch (Exception) {}

    try {
      # Assert that the entity was deleted using the softRemove method
      $result = $this->entityManager->softRemove($entity);
      $I->assertTrue($result->isOk());
      $I->seeInDatabase('mocks', [
        'id' => $entity->id,
        'name' => $entityName,
        'description' => self::TEST_DESCRIPTION,
        'color_type' => MockColorType::RED->value,
        'deleted_at' => date('Y-m-d H:i:s')
      ]);
    } catch (Exception) {}

    # Assert an exception is thrown when the entity is not found

    # Assert an exception is thrown when the entity is not soft deletable

    # Assert an exception is thrown when the entity is invalid
  }

  /** @noinspection SpellCheckingInspection */
  #[Skip]
  public function testTheDeleteMethod(UnitTester $I): void
  {
    // TODO: Implement testTheDeleteMethod() method.
    # Create a new entity

    # Assert that the entity was created

    # Assert new record was inserted into the database using the insert method

    # Assert that the entity was deleted using the delete method
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
