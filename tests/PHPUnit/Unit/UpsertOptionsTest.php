<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Management\Options\UpsertOptions;
use PHPUnit\Framework\TestCase;

final class UpsertOptionsTest extends TestCase
{
    public function testFromArrayPreservesReadonlyColumns(): void
    {
        $options = UpsertOptions::fromArray([
            'conflictPaths' => ['name'],
            'readonlyColumns' => ['id', 'createdAt', 'deletedAt'],
        ]);

        self::assertSame(['name'], $options->conflictPaths);
        self::assertSame(['id', 'createdAt', 'deletedAt'], $options->readonlyColumns);
    }
}
