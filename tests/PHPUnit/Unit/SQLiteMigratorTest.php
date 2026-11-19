<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Interfaces\IMigration;
use Assegai\Orm\Migrations\Migration;
use Assegai\Orm\Migrations\Migrator;
use Error;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;
use Throwable;
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

    public function testMigrationCallbacksAndRunnersDeclareThrowable(): void
    {
        foreach ([IMigration::class => ['up', 'down'], Migration::class => ['up', 'down'],
            Migrator::class => ['run', 'revert', 'redo', 'runAll', 'revertAll']] as $class => $methods) {
            foreach ($methods as $method) {
                self::assertMatchesRegularExpression(
                    '/@throws\\s+Throwable\\b/',
                    (string) (new ReflectionMethod($class, $method))->getDocComment(),
                    "$class::$method must declare the callback failure contract.",
                );
            }
        }
    }

    public static function callbackFailures(): iterable
    {
        foreach (['run' => ['up'], 'revert' => ['down'], 'redo' => ['up', 'down']] as $operation => $callbacks) {
            foreach ($callbacks as $callback) {
                foreach ([RuntimeException::class, Error::class] as $failureClass) {
                    yield "$operation/$callback/$failureClass" => [$operation, $callback, $failureClass];
                }
            }
        }
    }

    #[DataProvider('callbackFailures')]
    public function testCallbackFailuresPropagateUnchanged(string $operation, string $callback, string $failureClass): void
    {
        $failure = new $failureClass('Application migration failed.');
        $migration = new class($failure, $callback) extends Migration {
            public function __construct(private Throwable $failure, private string $callback) {}

            public function up(DataSource $dataSource): void
            {
                if ($this->callback === 'up') {
                    throw $this->failure;
                }
            }

            public function down(DataSource $dataSource): void
            {
                if ($this->callback === 'down') {
                    throw $this->failure;
                }
            }

            public function getName(): string
            {
                return 'CallbackFailure';
            }
        };
        $migrator = new Migrator($this->dataSource, $this->migrationsDirectory);
        $expectedRecords = $callback === 'down' ? 1 : 0;
        if ($expectedRecords === 1) {
            $migrator->run($migration);
        }

        $caught = null;
        try {
            $migrator->$operation($migration);
        } catch (Throwable $exception) {
            $caught = $exception;
        }

        self::assertSame($failure, $caught);
        self::assertSame($expectedRecords, $this->countMigrationRecords($migration->getName()));
    }

    public static function databaseFailureOperations(): iterable
    {
        foreach (['run', 'revert', 'runAll', 'revertAll'] as $operation) {
            yield $operation => [$operation];
        }
    }

    #[DataProvider('databaseFailureOperations')]
    public function testDatabaseFailuresStopMigrationBookkeeping(string $operation): void
    {
        $classOne = 'FailureAlpha' . str_replace('.', '', uniqid('', true));
        $classTwo = 'FailureBeta' . str_replace('.', '', uniqid('', true));
        $this->writeMigrationFixture('001_alpha.php', $classOne, 'failure_alpha', false);
        $this->writeMigrationFixture('002_beta.php', $classTwo, 'failure_beta', false);
        require_once $this->migrationsDirectory . '/001_alpha.php';
        require_once $this->migrationsDirectory . '/002_beta.php';
        $migrator = new Migrator($this->dataSource, $this->migrationsDirectory);
        $reverting = str_starts_with($operation, 'revert');

        if ($reverting) {
            $migrator->runAll();
            $this->dataSource->getClient()->exec('DROP TABLE failure_beta');
        } else {
            $this->dataSource->getClient()->exec('CREATE TABLE failure_alpha (id INTEGER PRIMARY KEY)');
        }

        $caught = null;
        try {
            if (str_ends_with($operation, 'All')) {
                $migrator->$operation();
            } else {
                $migrationClass = $reverting ? $classTwo : $classOne;
                $migrator->$operation(new $migrationClass());
            }
        } catch (PDOException $exception) {
            $caught = $exception;
        }

        self::assertInstanceOf(PDOException::class, $caught);
        self::assertSame($reverting ? 2 : 0, $this->countMigrationRecords($classOne, $classTwo));
        self::assertTrue($this->hasTable('failure_alpha'));
        self::assertFalse($this->hasTable('failure_beta'));
    }

    private function writeMigrationFixture(string $filename, string $className, string $tableName, bool $idempotent = true): void
    {
        $createGuard = $idempotent ? 'IF NOT EXISTS ' : '';
        $dropGuard = $idempotent ? 'IF EXISTS ' : '';
        $contents = <<<PHP
<?php

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Migrations\Migration;

class {$className} extends Migration
{
    public function up(DataSource \$dataSource): void
    {
        \$dataSource->getClient()->exec("CREATE TABLE {$createGuard}`{$tableName}` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `name` TEXT NOT NULL)");
    }

    public function down(DataSource \$dataSource): void
    {
        \$dataSource->getClient()->exec("DROP TABLE {$dropGuard}`{$tableName}`");
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
