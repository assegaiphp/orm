<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Migrations\Migrator;
use PHPUnit\Framework\TestCase;
use Unit\mocks\MockEntity;

final class SQLiteMigratorTest extends TestCase
{
    private string $dbPath;
    private string $workingDirectory;
    private string $migrationsDirectory;
    private DataSource $dataSource;

    protected function setUp(): void
    {
        $this->dbPath = dirname(__DIR__, 2) . '/_output/sqlite-migrator-' . uniqid('', true) . '.sqlite';
        @unlink($this->dbPath);

        $this->workingDirectory = dirname(__DIR__, 2) . '/_output/sqlite-migrator-' . uniqid('', true);
        $this->migrationsDirectory = $this->workingDirectory . '/database/migrations';

        if (!is_dir($this->migrationsDirectory) && !mkdir($concurrentDirectory = $this->migrationsDirectory, 0777, true) && !is_dir($concurrentDirectory)) {
            self::fail("Failed to create SQLite migrations directory {$this->migrationsDirectory}");
        }

        $this->dataSource = new DataSource(new DataSourceOptions(
            entities: [MockEntity::class],
            name: $this->dbPath,
            type: DataSourceType::SQLITE,
        ));
    }

    protected function tearDown(): void
    {
        unset($this->dataSource);
        @unlink($this->dbPath);
        $this->removeDirectory($this->workingDirectory);
    }

    public function testGenerateCreatesMigrationFileInsideDatabaseMigrationsDirectory(): void
    {
        $migrator = new Migrator($this->dataSource, $this->migrationsDirectory);
        $migrator->generate('create sqlite test table', $this->workingDirectory);

        $files = glob($this->migrationsDirectory . '/*.php');

        self::assertIsArray($files);
        self::assertCount(1, $files);
        self::assertStringEndsWith('create_sqlite_test_table.php', $files[0]);
        self::assertStringContainsString('class CreateSqliteTestTable extends Migration', (string) file_get_contents($files[0]));
    }

    public function testRunAllAndRevertAllManageTablesAndMigrationRecords(): void
    {
        $classOne = 'CreateSqliteMigratorAlpha' . substr(md5((string) microtime(true)), 0, 8);
        $classTwo = 'CreateSqliteMigratorBeta' . substr(md5((string) microtime(true) . 'beta'), 0, 8);
        $tableOne = 'orm_sqlite_migrator_alpha_' . substr(md5($classOne), 0, 8);
        $tableTwo = 'orm_sqlite_migrator_beta_' . substr(md5($classTwo), 0, 8);

        $this->writeMigrationFixture('2026_01_01_000001_create_sqlite_migrator_alpha.php', $classOne, $tableOne);
        $this->writeMigrationFixture('2026_01_01_000002_create_sqlite_migrator_beta.php', $classTwo, $tableTwo);

        $migrator = new Migrator($this->dataSource, $this->migrationsDirectory);
        $migrator->runAll();

        self::assertTrue($this->hasTable($tableOne));
        self::assertTrue($this->hasTable($tableTwo));
        self::assertSame(2, $this->countMigrationRecords($classOne, $classTwo));

        $list = $migrator->getListOfMigrationsAsString();
        self::assertStringContainsString($classOne, $list);
        self::assertStringContainsString($classTwo, $list);

        $migrator->revertAll();

        self::assertFalse($this->hasTable($tableOne));
        self::assertFalse($this->hasTable($tableTwo));
        self::assertSame(0, $this->countMigrationRecords($classOne, $classTwo));
    }

    private function writeMigrationFixture(string $filename, string $className, string $tableName): void
    {
        $contents = <<<PHP
<?php

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Migrations\Migration;

class {$className} extends Migration
{
    public function up(DataSource \$dataSource): void
    {
        \$dataSource->getClient()->exec("CREATE TABLE IF NOT EXISTS `{$tableName}` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` TEXT NOT NULL)");
    }

    public function down(DataSource \$dataSource): void
    {
        \$dataSource->getClient()->exec("DROP TABLE IF EXISTS `{$tableName}`");
    }
}
PHP;

        file_put_contents($this->migrationsDirectory . '/' . $filename, $contents);
    }

    private function hasTable(string $tableName): bool
    {
        $statement = $this->dataSource->getClient()->prepare(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table LIMIT 1"
        );
        $statement->execute(['table' => $tableName]);

        return $statement->fetchColumn() !== false;
    }

    private function countMigrationRecords(string ...$migrationNames): int
    {
        $placeholders = implode(',', array_fill(0, count($migrationNames), '?'));
        $statement = $this->dataSource->getClient()->prepare(
            "SELECT COUNT(*) FROM `__assegai_schema_migrations` WHERE `migration` IN ({$placeholders})"
        );
        $statement->execute($migrationNames);

        return (int) $statement->fetchColumn();
    }

    private function removeDirectory(string $path): void
    {
        if ($path === '' || !is_dir($path)) {
            return;
        }

        $items = scandir($path) ?: [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $path . '/' . $item;

            if (is_dir($itemPath)) {
                $this->removeDirectory($itemPath);
                continue;
            }

            @unlink($itemPath);
        }

        @rmdir($path);
    }
}
