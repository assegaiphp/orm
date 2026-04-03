<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Migrations\MigrationsList;
use Assegai\Orm\Migrations\SchemaMigrationsEntity;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

final class MigrationsListTest extends TestCase
{
    public function testReturnsTheChronologicallyFirstMigration(): void
    {
        $reflection = new ReflectionClass(MigrationsList::class);
        $list = $reflection->newInstanceWithoutConstructor();

        $property = new ReflectionProperty(MigrationsList::class, 'listOfMigrations');

        $first = new SchemaMigrationsEntity();
        $first->name = '2026_01_01_000000_create_users';
        $first->ranOn = '2026-01-01 00:00:00';

        $second = new SchemaMigrationsEntity();
        $second->name = '2026_01_02_000000_create_posts';
        $second->ranOn = '2026-01-02 00:00:00';

        $property->setValue($list, [$second, $first]);

        self::assertSame($first, $list->getFirstRun());
        self::assertSame($second, $list->getLastRun());
    }
}
