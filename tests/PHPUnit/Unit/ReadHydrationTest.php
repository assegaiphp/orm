<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\PasswordColumn;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindManyOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Management\Repository;
use Assegai\Orm\Queries\Sql\ColumnType;
use Assegai\Orm\Queries\Sql\SQLQuery;
use DateTime;
use PDO;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ReadHydrationTest extends TestCase
{
    private DataSource $dataSource;
    private string $databasePath;

    protected function setUp(): void
    {
        $this->databasePath = sys_get_temp_dir() . '/assegai-read-hydration-' . uniqid('', true) . '.sqlite';
        $this->cleanupSqliteFiles($this->databasePath);
        $this->dataSource = new DataSource(new DataSourceOptions(
            entities: [HydratedRecordEntity::class, HydrationCompanionEntity::class],
            name: $this->databasePath,
            type: DataSourceType::SQLITE,
        ));
        $this->dataSource->getClient()->exec(
            'CREATE TABLE hydrated_records (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                display_name TEXT NOT NULL,
                status TEXT NOT NULL,
                occurred_at TEXT NOT NULL,
                enabled INTEGER NOT NULL,
                credential_hash TEXT NOT NULL
            )'
        );
        $this->dataSource->getClient()->exec(
            "INSERT INTO hydrated_records (display_name, status, occurred_at, enabled, credential_hash)
             VALUES ('Hydrated row', 'active', '2026-08-25 09:30:00', 1, 'stored-hash')"
        );
    }

    protected function tearDown(): void
    {
        $this->dataSource->disconnect();
        $this->cleanupSqliteFiles($this->databasePath);
    }

    public function testHydrationIsOptInForMultiEntityDataSources(): void
    {
        $manager = $this->dataSource->manager;

        $raw = $manager->find(HydratedRecordEntity::class)->getData()[0];
        self::assertInstanceOf(stdClass::class, $raw);

        $entity = $manager->find(
            HydratedRecordEntity::class,
            new FindOptions(hydrate: true),
        )->getData()[0];

        $this->assertHydratedRecord($entity);
        self::assertFalse(isset($entity->credentialHash));
    }

    public function testAllPublicReadEntryPointsHonorHydration(): void
    {
        $manager = $this->dataSource->manager;
        $repository = new Repository(HydratedRecordEntity::class, $manager);
        $this->dataSource->getClient()->exec(
            "INSERT INTO hydrated_records (display_name, status, occurred_at, enabled, credential_hash)
             VALUES ('Second row', 'active', '2026-08-25 10:30:00', 0, 'second-hash')"
        );

        $fromFindBy = $manager->findBy(
            HydratedRecordEntity::class,
            new FindWhereOptions(['id' => 1], hydrate: true),
        )->getData()[0];
        $fromRepository = $repository->find(['where' => ['id' => 1], 'hydrate' => true])->getData()[0];
        $fromFindOne = $repository->findOne(['where' => ['id' => 1], 'hydrate' => true])->getData();
        $fromFindAndCount = $repository->findAndCount(new FindManyOptions(hydrate: true));
        $fromFindAndCountBy = $repository->findAndCountBy([
            'conditions' => ['id' => 1],
            'hydrate' => true,
        ]);

        $this->assertHydratedRecord($fromFindBy);
        $this->assertHydratedRecord($fromRepository);
        $this->assertHydratedRecord($fromFindOne);
        $this->assertHydratedRecord($fromFindAndCount->getData()[0]);
        $this->assertHydratedRecord($fromFindAndCountBy->getData()[0]);
        self::assertSame(2, $fromFindAndCount->getTotal());
        self::assertSame(1, $fromFindAndCountBy->getTotal());
    }

    public function testFindByShorthandSeparatesConditionsFromHydrationMetadata(): void
    {
        $managerResult = $this->dataSource->manager->findBy(
            HydratedRecordEntity::class,
            ['name' => 'Hydrated row', 'hydrate' => true],
        )->getData()[0];
        $repository = new Repository(HydratedRecordEntity::class, $this->dataSource->manager);
        $repositoryResult = $repository->findBy([
            'id' => 1,
            'hydrate' => true,
        ])->getData()[0];
        $counted = $repository->findAndCountBy([
            'id' => 1,
            'hydrate' => true,
        ]);

        $this->assertHydratedRecord($managerResult);
        $this->assertHydratedRecord($repositoryResult);
        $this->assertHydratedRecord($counted->getData()[0]);
        self::assertSame(1, $counted->getTotal());

        $options = FindWhereOptions::fromArray([
            'id' => 1,
            'hydrate' => true,
            'with_real_total' => true,
            'exclude' => [],
        ]);
        self::assertSame(['id' => 1], $options->conditions);
        self::assertTrue($options->hydrate);
        self::assertTrue($options->withRealTotal);
        self::assertTrue($options->excludeIsExplicit);

        $reservedColumn = FindWhereOptions::fromArray([
            'conditions' => ['hydrate' => true],
            'hydrate' => false,
        ]);
        self::assertSame(['hydrate' => true], $reservedColumn->conditions);
        self::assertFalse($reservedColumn->hydrate);
    }

    public function testHydrationDoesNotDependOnTheDataSourceFetchClass(): void
    {
        $manager = new EntityManager(
            $this->dataSource,
            SQLQuery::forConnection(
                db: $this->dataSource->getClient(),
                fetchClass: HydratedRecordEntity::class,
                fetchMode: PDO::FETCH_CLASS,
                dialect: $this->dataSource->getDialect(),
            ),
        );

        $entity = $manager->find(
            HydratedRecordEntity::class,
            new FindOptions(hydrate: true),
        )->getData()[0];

        $this->assertHydratedRecord($entity);
    }

    public function testHydrationPreservesOptionRoundTripsAndExclusions(): void
    {
        $options = FindOptions::fromArray(FindOptions::toArray(new FindOptions(
            exclude: ['enabled'],
            hydrate: true,
        )));
        $entity = $this->dataSource->manager->find(HydratedRecordEntity::class, $options)->getData()[0];

        self::assertTrue($options->hydrate);
        self::assertInstanceOf(HydratedRecordEntity::class, $entity);
        self::assertFalse(isset($entity->enabled));
        self::assertSame('stored-hash', $entity->credentialHash);

        $trustedEntity = $this->dataSource->manager->find(
            HydratedRecordEntity::class,
            new FindOptions(exclude: [], hydrate: true),
        )->getData()[0];
        self::assertSame('stored-hash', $trustedEntity->credentialHash);
    }

    private function assertHydratedRecord(mixed $entity): void
    {
        self::assertInstanceOf(HydratedRecordEntity::class, $entity);
        self::assertSame('Hydrated row', $entity->name);
        self::assertSame(HydrationStatus::ACTIVE, $entity->status);
        self::assertInstanceOf(DateTime::class, $entity->occurredAt);
        self::assertSame('2026-08-25 09:30:00', $entity->occurredAt->format('Y-m-d H:i:s'));
        self::assertTrue($entity->enabled);
    }

    private function cleanupSqliteFiles(string $path): void
    {
        foreach ([$path, $path . '-wal', $path . '-shm'] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

enum HydrationStatus: string
{
    case ACTIVE = 'active';
}

#[Entity(table: 'hydrated_records')]
class HydratedRecordEntity
{
    #[PrimaryGeneratedColumn]
    public ?int $id = null;

    #[Column(name: 'display_name', alias: 'displayName', type: ColumnType::TEXT, nullable: false)]
    public string $name = '';

    #[Column(type: ColumnType::ENUM, nullable: false, enum: HydrationStatus::class)]
    public HydrationStatus $status = HydrationStatus::ACTIVE;

    #[Column(type: ColumnType::DATETIME, nullable: false)]
    public ?DateTime $occurredAt = null;

    #[Column(type: ColumnType::BOOLEAN, nullable: false)]
    public bool $enabled = false;

    #[PasswordColumn(name: 'credential_hash')]
    public string $credentialHash = '';
}

#[Entity(table: 'hydration_companions')]
class HydrationCompanionEntity
{
    #[PrimaryGeneratedColumn]
    public ?int $id = null;
}
