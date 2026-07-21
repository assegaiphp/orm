<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\PasswordColumn;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\DBFactory;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\UpsertOptions;
use Assegai\Orm\Queries\Sql\ColumnType;
use Assegai\Orm\Queries\Sql\SQLExpression;
use Assegai\Orm\Queries\Sql\SQLQuery;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

final class SecurityHardeningTest extends TestCase
{
    private ?DataSource $dataSource = null;
    private string $databasePath;

    protected function setUp(): void
    {
        $this->databasePath = sys_get_temp_dir() . '/assegai-security-' . uniqid('', true) . '.sqlite';
        $this->cleanupSqliteFiles($this->databasePath);
        $this->dataSource = new DataSource(new DataSourceOptions(
            entities: [SecureAccountEntity::class],
            name: $this->databasePath,
            type: DataSourceType::SQLITE,
        ));
        $this->dataSource->getClient()->exec(
            'CREATE TABLE secure_accounts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL UNIQUE,
                credential_hash TEXT NOT NULL
            )'
        );
    }

    protected function tearDown(): void
    {
        unset($_GET['limit'], $_GET['skip']);
        $this->dataSource?->disconnect();
        $this->cleanupSqliteFiles($this->databasePath);
    }

    public function testPasswordColumnHasNoKnownDefaultAndIsNotNullable(): void
    {
        $definition = (new PasswordColumn(name: 'credential_hash'))->getSqlDefinition(SQLDialect::SQLITE);

        self::assertStringContainsString('NOT NULL', $definition);
        self::assertStringNotContainsString('DEFAULT', $definition);
    }

    public function testCustomPasswordColumnIsHashedAndRedactedAcrossInsertReadAndUpdate(): void
    {
        $manager = $this->manager();
        $insert = $manager->insert(SecureAccountEntity::class, [
            'email' => 'first@example.test',
            'credentialHash' => 'initial-secret',
        ]);

        $stored = $this->fetchCredential('first@example.test');
        self::assertTrue(password_verify('initial-secret', $stored));
        self::assertArrayNotHasKey('credentialHash', (array)$insert->generatedMaps);

        $found = $manager->find(SecureAccountEntity::class);
        self::assertArrayNotHasKey('credentialHash', (array)$found->getData()[0]);

        $update = $manager->update(
            SecureAccountEntity::class,
            ['credentialHash' => 'replacement-secret'],
            ['email' => 'first@example.test'],
        );

        self::assertTrue(password_verify('replacement-secret', $this->fetchCredential('first@example.test')));
        self::assertArrayNotHasKey('credentialHash', (array)$update->generatedMaps);
    }

    public function testSqliteUpsertHashesCustomPasswordColumnsAndRedactsResults(): void
    {
        $result = $this->manager()->upsert(
            SecureAccountEntity::class,
            (object)[
                'email' => 'upsert@example.test',
                'credentialHash' => 'upsert-secret',
            ],
            new UpsertOptions(conflictPaths: ['email']),
        );

        self::assertTrue(password_verify('upsert-secret', $this->fetchCredential('upsert@example.test')));
        self::assertArrayNotHasKey('credentialHash', (array)$result->generatedMaps);

        $this->manager()->upsert(
            SecureAccountEntity::class,
            (object)[
                'email' => 'upsert@example.test',
                'credentialHash' => 'updated-upsert-secret',
            ],
            new UpsertOptions(conflictPaths: ['email']),
        );
        self::assertTrue(password_verify('updated-upsert-secret', $this->fetchCredential('upsert@example.test')));
    }

    public function testBulkInsertHashesCustomPasswordColumns(): void
    {
        $this->manager()->insert(SecureAccountEntity::class, [
            ['email' => 'bulk-one@example.test', 'credentialHash' => 'bulk-secret-one'],
            ['email' => 'bulk-two@example.test', 'credentialHash' => 'bulk-secret-two'],
        ]);

        self::assertTrue(password_verify('bulk-secret-one', $this->fetchCredential('bulk-one@example.test')));
        self::assertTrue(password_verify('bulk-secret-two', $this->fetchCredential('bulk-two@example.test')));
    }

    public function testSelectRejectsRawStringsAndAcceptsExplicitExpressions(): void
    {
        $query = SQLQuery::forConnection(new PDO('sqlite::memory:'));

        $this->expectException(InvalidArgumentException::class);
        $query->select()->all(['name, (SELECT sqlite_version()) AS injected']);
    }

    public function testSelectAllowsExplicitRawExpressions(): void
    {
        $query = SQLQuery::forConnection(new PDO('sqlite::memory:'));
        $query->select()->all([new SQLExpression('COUNT(*) AS total')])->from('secure_accounts');

        self::assertSame('SELECT COUNT(*) AS total FROM "secure_accounts"', (string)$query);
    }

    public function testFindIgnoresHttpPaginationAndCapsExplicitLimits(): void
    {
        $_GET['limit'] = '1000000000';
        $_GET['skip'] = '200';

        $defaultResult = $this->manager()->find(SecureAccountEntity::class);
        self::assertStringContainsString('LIMIT 0,10', $defaultResult->getRaw());

        $boundedResult = $this->manager()->find(
            SecureAccountEntity::class,
            new FindOptions(limit: PHP_INT_MAX, skip: -100),
        );
        self::assertStringContainsString('LIMIT 0,1000', $boundedResult->getRaw());
    }

    public function testDataSourceSerializationRedactsPasswordAndSqlServerVerifiesCertificates(): void
    {
        $options = new DataSourceOptions([], 'production', password: 'database-secret');

        self::assertSame('[REDACTED]', $options->toArray()['password']);
        self::assertStringNotContainsString('database-secret', (string)$options);
        self::assertStringNotContainsString('database-secret', json_encode($options));
        self::assertSame(
            'sqlsrv:Server=db.example.test,1433;Database=production;Encrypt=yes;TrustServerCertificate=no',
            DBFactory::buildMsSqlDsn('db.example.test', 1433, 'production'),
        );
        self::assertStringEndsWith(
            'TrustServerCertificate=yes',
            DBFactory::buildMsSqlDsn('db.example.test', 1433, 'production', true),
        );
    }

    private function manager(): EntityManager
    {
        return new EntityManager(
            $this->dataSource,
            SQLQuery::forConnection(
                $this->dataSource->getClient(),
                fetchClass: SecureAccountEntity::class,
                fetchMode: PDO::FETCH_CLASS,
                dialect: SQLDialect::SQLITE,
            ),
        );
    }

    private function fetchCredential(string $email): string
    {
        $statement = $this->dataSource->getClient()->prepare(
            'SELECT credential_hash FROM secure_accounts WHERE email = ?'
        );
        $statement->execute([$email]);

        return (string)$statement->fetchColumn();
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

#[Entity(table: 'secure_accounts')]
class SecureAccountEntity
{
    #[PrimaryGeneratedColumn]
    public ?int $id = null;

    #[Column(name: 'email', type: ColumnType::TEXT, nullable: false, isUnique: true)]
    public string $email = '';

    #[PasswordColumn(name: 'credential_hash')]
    public string $credentialHash = '';
}
