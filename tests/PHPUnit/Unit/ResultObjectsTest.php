<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Queries\QueryBuilder\Results\InsertResult;
use Assegai\Orm\Queries\QueryBuilder\Results\UpdateResult;
use Assegai\Orm\Results\FindResult;
use PHPUnit\Framework\TestCase;

final class ResultObjectsTest extends TestCase
{
    public function testInsertResultExposesIdentifierAndGeneratedMapAccessors(): void
    {
        $entity = (object) ['id' => 7, 'name' => 'Grace'];
        $result = new InsertResult($entity, 'INSERT INTO users ...', $entity, affected: 1);

        self::assertSame($entity, $result->getData());
        self::assertSame($entity, $result->getIdentifiers());
        self::assertSame($entity, $result->getGeneratedMaps());
        self::assertSame(1, $result->getTotalAffectedRows());
    }

    public function testUpdateResultExposesIdentifierAndGeneratedMapAccessors(): void
    {
        $entity = (object) ['id' => 9, 'name' => 'Ada'];
        $result = new UpdateResult('UPDATE users ...', 1, $entity, $entity);

        self::assertSame($entity, $result->getIdentifiers());
        self::assertSame($entity, $result->getGeneratedMaps());
        self::assertSame($entity, $result->getData());
        self::assertSame(1, $result->getTotalAffectedRows());
    }

    public function testPublicFindResultUsesTheDeveloperFacingNamespace(): void
    {
        $entity = (object) ['id' => 11, 'name' => 'Lin'];
        $result = new FindResult(raw: 'SELECT * FROM users', data: [$entity], affected: 1, total: 1);

        self::assertSame($entity, $result->getFirst());
        self::assertSame(1, $result->getTotal());
        self::assertSame(1, $result->getTotalAffectedRows());
        self::assertFalse($result->isEmpty());
    }
}
