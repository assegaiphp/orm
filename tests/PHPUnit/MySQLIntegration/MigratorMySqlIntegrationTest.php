<?php

namespace Tests\PHPUnit\MySQLIntegration;

use Assegai\Orm\Migrations\Migrator;
use Tests\PHPUnit\Support\MySqlIntegrationTestCase;

final class MigratorMySqlIntegrationTest extends MySqlIntegrationTestCase
{
    private string $workingDirectory;
    private string $migrationsDirectory;

    /** @var string[] */
    private array $migrationClasses = [];

    /** @var string[] */
    private array $migrationTables = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->workingDirectory = dirname(__DIR__, 2) . '/_output/mysql-migrator-' . uniqid('', true);
        $this->migrationsDirectory = $this->workingDirectory . '/database/migrations';

        if (!is_dir($this->migrationsDirectory) && !mkdir($concurrentDirectory = $this->migrationsDirectory, 0777, true) && !is_dir($concurrentDirectory)) {
            self::fail("Failed to create migration test directory {$this->migrationsDirectory}");
        }
    }

    protected function tearDown(): void
    {
        foreach (array_reverse($this->migrationTables) as $tableName) {
            $this->dataSource->getClient()->exec("DROP TABLE IF EXISTS `{$this->databaseName}`.`{$tableName}`");
        }

        if ($this->migrationClasses !== []) {
            $placeholders = implode(',', array_fill(0, count($this->migrationClasses), '?'));
            $statement = $this->dataSource->getClient()->prepare(
                "DELETE FROM `{$this->databaseName}`.`__assegai_schema_migrations` WHERE `migration` IN ({$placeholders})"
            );
            $statement->execute($this->migrationClasses);
        }

        $this->removeDirectory($this->workingDirectory);
        parent::tearDown();
    }

    public function testGenerateMigrationFileNameIncludesTimestampPrefix(): void
    {
        $migrator = new Migrator($this->dataSource, $this->migrationsDirectory);

        self::assertSame('2023_11_14_221320_create_test_table.php', $migrator->generateMigrationFileName('create test table', 1700000000));
    }

    public function testGenerateCreatesMigrationFileInDatabaseMigrationsDirectory(): void
    {
        $migrator = new Migrator($this->dataSource, $this->migrationsDirectory);
        $migrator->generate('create test table', $this->workingDirectory);

        $files = glob($this->migrationsDirectory . '/*.php');

        self::assertIsArray($files);
        self::assertCount(1, $files);
        self::assertStringEndsWith('create_test_table.php', $files[0]);
        self::assertStringContainsString('class CreateTestTable extends Migration', (string) file_get_contents($files[0]));
    }

    public function testRunAllAndRevertAllManageTablesAndMigrationRecords(): void
    {
        $classOne = 'CreateMysqlMigratorAlpha' . substr(md5((string) microtime(true)), 0, 8);
        $classTwo = 'CreateMysqlMigratorBeta' . substr(md5((string) microtime(true) . 'beta'), 0, 8);
        $tableOne = 'mysql_migrator_alpha_' . substr(md5($classOne), 0, 8);
        $tableTwo = 'mysql_migrator_beta_' . substr(md5($classTwo), 0, 8);

        $this->writeMigrationFixture('2026_01_01_000001_create_mysql_migrator_alpha.php', $classOne, $tableOne);
        $this->writeMigrationFixture('2026_01_01_000002_create_mysql_migrator_beta.php', $classTwo, $tableTwo);

        $this->migrationClasses = [$classOne, $classTwo];
        $this->migrationTables = [$tableOne, $tableTwo];

        $migrator = new Migrator($this->dataSource, $this->migrationsDirectory);
        $migrator->runAll();

        self::assertTrue($this->hasTable($tableOne));
        self::assertTrue($this->hasTable($tableTwo));
        self::assertSame(2, $this->countMigrationRecords($classOne, $classTwo));

        $list = $migrator->getListOfMigrationsAsString();
        self::assertStringContainsString($classOne, $list);
        self::assertStringContainsString($classTwo, $list);

        $migrator->revertAll($this->migrationsDirectory);

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
        \$dataSource->getClient()->exec("CREATE TABLE IF NOT EXISTS `{$this->databaseName}`.`{$tableName}` (`id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) NOT NULL)");
    }

    public function down(DataSource \$dataSource): void
    {
        \$dataSource->getClient()->exec("DROP TABLE IF EXISTS `{$this->databaseName}`.`{$tableName}`");
    }
}
PHP;

        file_put_contents($this->migrationsDirectory . '/' . $filename, $contents);
    }

    private function hasTable(string $tableName): bool
    {
        $statement = $this->dataSource->getClient()->prepare(
            'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table LIMIT 1'
        );
        $statement->execute([
            'database' => $this->databaseName,
            'table' => $tableName,
        ]);

        return $statement->fetchColumn() !== false;
    }

    private function countMigrationRecords(string ...$migrationNames): int
    {
        $placeholders = implode(',', array_fill(0, count($migrationNames), '?'));
        $statement = $this->dataSource->getClient()->prepare(
            "SELECT COUNT(*) FROM `{$this->databaseName}`.`__assegai_schema_migrations` WHERE `migration` IN ({$placeholders})"
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
