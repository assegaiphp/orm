<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Attributes\Relations\JoinColumn;
use Assegai\Orm\Queries\Sql\ColumnType;
use PHPUnit\Framework\TestCase;

final class JoinColumnTest extends TestCase
{
    public function testDefaultsStayBackendNeutral(): void
    {
        $joinColumn = new JoinColumn(name: 'author_id');

        self::assertSame('author_id', $joinColumn->name);
        self::assertSame('id', $joinColumn->effectiveReferencedColumnName);
        self::assertSame(ColumnType::BIGINT_UNSIGNED, $joinColumn->type);
    }

    public function testExplicitTypeOverridesDefault(): void
    {
        $joinColumn = new JoinColumn(name: 'author_id', type: ColumnType::UUID);

        self::assertSame(ColumnType::UUID, $joinColumn->type);
    }
}
