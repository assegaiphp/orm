<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Queries\Sql\SQLFindOptions;
use PHPUnit\Framework\TestCase;

final class SQLFindOptionsTest extends TestCase
{
    public function testItStoresSqlQueryBuilderSpecificOptions(): void
    {
        $options = new SQLFindOptions(
            select: ['id', 'name'],
            relations: ['profile'],
            join: ['profile' => 'profiles'],
            exclude: ['password'],
        );

        self::assertSame(['id', 'name'], $options->select);
        self::assertSame(['profile'], $options->relations);
        self::assertSame(['profile' => 'profiles'], $options->join);
        self::assertSame(['password'], $options->exclude);
    }
}
