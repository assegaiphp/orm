<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Attributes\InjectRepository;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Queries\Sql\ColumnType;
use Assegai\Orm\Support\OrmRuntime;
use PHPUnit\Framework\TestCase;

#[Entity(table: 'runtime_users', dataSource: 'runtime_app')]
final class RuntimeInjectRepositoryUser
{
    #[PrimaryGeneratedColumn]
    public int $id;

    #[Column(name: 'email', type: ColumnType::VARCHAR, nullable: false)]
    public string $email;
}

#[Entity(table: 'legacy_runtime_users', database: 'legacy_app')]
final class LegacyRuntimeInjectRepositoryUser
{
    #[PrimaryGeneratedColumn]
    public int $id;

    #[Column(name: 'email', type: ColumnType::VARCHAR, nullable: false)]
    public string $email;
}

final class InjectRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        OrmRuntime::configure([]);
        OrmRuntime::setModuleConfig([]);

        parent::tearDown();
    }

    public function testResolvesDriverFromConfiguredDatabaseName(): void
    {
        OrmRuntime::configure([
            'databases' => [
                'sqlite' => [
                    'runtime_app' => [
                        'path' => ':memory:',
                    ],
                ],
            ],
        ]);

        $attribute = new InjectRepository(RuntimeInjectRepositoryUser::class);

        self::assertSame(DataSourceType::SQLITE, $attribute->dataSource->getOptions()->type);
        self::assertSame('runtime_app', $attribute->dataSource->getOptions()->name);
    }

    public function testStillSupportsLegacyDatabaseMetadataAsDataSourceName(): void
    {
        OrmRuntime::configure([
            'databases' => [
                'sqlite' => [
                    'legacy_app' => [
                        'path' => ':memory:',
                    ],
                ],
            ],
        ]);

        $attribute = new InjectRepository(LegacyRuntimeInjectRepositoryUser::class);

        self::assertSame(DataSourceType::SQLITE, $attribute->dataSource->getOptions()->type);
        self::assertSame('legacy_app', $attribute->dataSource->getOptions()->name);
    }
}
