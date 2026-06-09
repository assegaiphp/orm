<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Columns\UpdateDateColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Management\Options\UpdateOptions;
use Assegai\Orm\Queries\Sql\ColumnType;
use Assegai\Orm\Queries\Sql\SQLQuery;
use DateTime;
use DateTimeZone;
use PDO;
use PHPUnit\Framework\TestCase;

final class EntityManagerOnUpdateTest extends TestCase
{
    private const INITIAL_UPDATED_AT = '2000-01-01 00:00:00';

    private ?DataSource $dataSource = null;
    private string $databasePath;

    protected function setUp(): void
    {
        $this->databasePath = sys_get_temp_dir() . '/assegai-on-update-' . uniqid('', true) . '.sqlite';
        $this->cleanupSqliteFiles($this->databasePath);
        $this->dataSource = new DataSource(new DataSourceOptions(
            entities: [OnUpdateManagedEntity::class],
            name: $this->databasePath,
            type: DataSourceType::SQLITE,
        ));

        $this->dataSource->getClient()->exec(
            'CREATE TABLE on_update_entities (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                description TEXT NOT NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                deleted_at TEXT NULL
            )'
        );
    }

    protected function tearDown(): void
    {
        $this->dataSource?->disconnect();
        $this->cleanupSqliteFiles($this->databasePath);
    }

    public function testUpdateAppliesOnUpdateColumnWhenCallerOmitsIt(): void
    {
        $this->insertFixture('Ada', 'Before update');

        $result = $this->createManager()->update(
            OnUpdateManagedEntity::class,
            ['description' => 'After update'],
            ['name' => 'Ada'],
        );

        $row = $this->fetchRow('Ada');

        self::assertTrue($result->isOk());
        self::assertStringContainsString('"updated_at"=CURRENT_TIMESTAMP', $result->getRaw());
        self::assertSame('After update', $row['description']);
        self::assertNotSame(self::INITIAL_UPDATED_AT, $row['updated_at']);
    }

    public function testUpdateKeepsExplicitOnUpdateColumnAssignment(): void
    {
        $this->insertFixture('Grace', 'Before explicit update');

        $result = $this->createManager()->update(
            OnUpdateManagedEntity::class,
            [
                'description' => 'After explicit update',
                'updatedAt' => new DateTime('2026-01-02 03:04:05', new DateTimeZone('UTC')),
            ],
            ['name' => 'Grace'],
            new UpdateOptions(readonlyColumns: ['id', 'createdAt', 'deletedAt']),
        );

        $row = $this->fetchRow('Grace');

        self::assertTrue($result->isOk());
        self::assertStringNotContainsString('"updated_at"=CURRENT_TIMESTAMP', $result->getRaw());
        self::assertSame('After explicit update', $row['description']);
        self::assertSame('2026-01-02 03:04:05', $row['updated_at']);
    }

    public function testSqliteUpsertAppliesOnUpdateColumnOnConflict(): void
    {
        $this->insertFixture('Linus', 'Before upsert');

        $entity = new OnUpdateManagedEntity();
        $entity->name = 'Linus';
        $entity->description = 'After upsert';

        $result = $this->createManager()->upsert(OnUpdateManagedEntity::class, $entity, ['name']);
        $row = $this->fetchRow('Linus');

        self::assertTrue($result->isOk());
        self::assertStringContainsString('ON CONFLICT ("name") DO UPDATE SET', $result->getRaw());
        self::assertStringContainsString('"updated_at"=CURRENT_TIMESTAMP', $result->getRaw());
        self::assertSame('After upsert', $row['description']);
        self::assertNotSame(self::INITIAL_UPDATED_AT, $row['updated_at']);
    }

    private function createManager(): EntityManager
    {
        return new EntityManager(
            connection: $this->dataSource,
            query: new SQLQuery(
                db: $this->dataSource->getClient(),
                fetchClass: OnUpdateManagedEntity::class,
                fetchMode: PDO::FETCH_CLASS,
                dialect: SQLDialect::SQLITE,
            ),
        );
    }

    private function insertFixture(string $name, string $description): void
    {
        $statement = $this->dataSource->getClient()->prepare(
            'INSERT INTO on_update_entities (name, description, created_at, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP, ?)'
        );
        $statement->execute([$name, $description, self::INITIAL_UPDATED_AT]);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchRow(string $name): array
    {
        $statement = $this->dataSource->getClient()->prepare(
            'SELECT name, description, updated_at FROM on_update_entities WHERE name = ?'
        );
        $statement->execute([$name]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    private function cleanupSqliteFiles(string $path): void
    {
        foreach ([$path, $path . '-wal', $path . '-shm'] as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}

#[Entity(table: 'on_update_entities')]
class OnUpdateManagedEntity
{
    #[PrimaryGeneratedColumn]
    public ?int $id = null;

    #[Column(type: ColumnType::VARCHAR, nullable: false)]
    public string $name = '';

    #[Column(type: ColumnType::TEXT, nullable: false)]
    public string $description = '';

    #[UpdateDateColumn]
    public ?DateTime $updatedAt = null;
}
