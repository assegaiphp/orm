<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Support\OrmRuntime;
use PHPUnit\Framework\TestCase;

final class OrmRuntimeTest extends TestCase
{
    protected function tearDown(): void
    {
        OrmRuntime::configure([]);
        OrmRuntime::setModuleConfig([]);

        parent::tearDown();
    }

    public function testPrefersExplicitOrmConfigForDatabaseDefinitions(): void
    {
        OrmRuntime::configure([
            'databases' => [
                'sqlite' => [
                    'app' => [
                        'path' => '/tmp/demo.sqlite',
                    ],
                ],
            ],
        ]);

        self::assertSame(
            [
                'sqlite' => [
                    'app' => [
                        'path' => '/tmp/demo.sqlite',
                    ],
                ],
            ],
            OrmRuntime::databaseConfigs(),
        );
    }

    public function testResolvesDatabaseTypeFromConfiguredDatabaseName(): void
    {
        OrmRuntime::configure([
            'databases' => [
                'sqlite' => [
                    'app' => [
                        'path' => '/tmp/demo.sqlite',
                    ],
                ],
            ],
        ]);

        self::assertSame(DataSourceType::SQLITE, OrmRuntime::resolveDatabaseType('app'));
        self::assertNull(OrmRuntime::resolveDatabaseType('missing'));
    }
    public function testReadsModuleConfigFromOrmRuntimeState(): void
    {
        OrmRuntime::setModuleConfig([
            'data_source' => 'primary',
            'converters' => ['App\\Orm\\MoneyConverter'],
        ]);

        self::assertSame('primary', OrmRuntime::moduleConfig('data_source'));
        self::assertSame(['App\\Orm\\MoneyConverter'], OrmRuntime::moduleConfig('converters'));
    }

    public function testJoinPathBuildsPortablePaths(): void
    {
        $path = OrmRuntime::joinPath('/srv/app/', '/storage', 'sqlite', 'app.sqlite');

        self::assertSame('/srv/app/storage/sqlite/app.sqlite', $path);
    }

    public function testEnvironmentFallsBackToPhpEnvironmentWhenCoreIsUnavailable(): void
    {
        $previousEnv = $_ENV['ENV'] ?? null;
        $hadEnv = array_key_exists('ENV', $_ENV);
        $_ENV['ENV'] = 'production';

        try {
            self::assertTrue(OrmRuntime::isProduction());
            self::assertSame('PRODUCTION', OrmRuntime::environment());
        } finally {
            if ($hadEnv) {
                $_ENV['ENV'] = $previousEnv;
            } else {
                unset($_ENV['ENV']);
            }
        }
    }
}
