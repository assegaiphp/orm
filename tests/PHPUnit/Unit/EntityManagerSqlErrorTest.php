<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Exceptions\GeneralSQLQueryException;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Queries\Sql\SQLQueryResult;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

final class EntityManagerSqlErrorTest extends TestCase
{
    public function testThrowableSqlErrorsArePreservedAsPreviousExceptions(): void
    {
        $previous = new RuntimeException('Driver failed.');
        $exception = self::createGeneralSqlQueryException(new SQLQueryResult([], [$previous]));

        self::assertSame($previous, $exception->getPrevious());
    }

    public function testNonThrowableSqlErrorShapesAreNotPassedAsPreviousExceptions(): void
    {
        $exception = self::createGeneralSqlQueryException(new SQLQueryResult([], [
            [
                'code' => 'HY000',
                'info' => ['driver reported execute=false'],
            ],
        ]));

        self::assertNull($exception->getPrevious());
    }

    private static function createGeneralSqlQueryException(SQLQueryResult $result): GeneralSQLQueryException
    {
        $reflection = new ReflectionClass(EntityManager::class);
        $entityManager = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('newGeneralSqlQueryException');
        $exception = $method->invoke($entityManager, null, $result);

        self::assertInstanceOf(GeneralSQLQueryException::class, $exception);

        return $exception;
    }
}
