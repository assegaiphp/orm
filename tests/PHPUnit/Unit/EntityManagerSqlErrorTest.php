<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Exceptions\GeneralSQLQueryException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Queries\Sql\SQLQueryResult;
use PDOException;
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

    public function testDriverSqlExceptionsArePreferredOverSyntheticOrmErrors(): void
    {
        $syntheticError = new ORMException('General SQL error.');
        $driverError = new PDOException('Driver failed.');

        self::withEnvironment('development', function () use ($syntheticError, $driverError): void {
            $exception = self::createGeneralSqlQueryException(new SQLQueryResult([], [$syntheticError, $driverError]));

            self::assertSame($driverError, $exception->getPrevious());
        });
    }

    public function testProductionSqlErrorsPreserveSanitizedOrmExceptionInsteadOfDriverException(): void
    {
        $syntheticError = new ORMException('General SQL error - Internal server error.');
        $driverError = new PDOException('Driver failed with private connection details.');

        self::withEnvironment('production', function () use ($syntheticError, $driverError): void {
            $exception = self::createGeneralSqlQueryException(new SQLQueryResult([], [$syntheticError, $driverError]));

            self::assertSame($syntheticError, $exception->getPrevious());
        });
    }

    public function testDevelopmentPublicResultErrorsKeepDriverExceptions(): void
    {
        $syntheticError = new ORMException('General SQL error.');
        $driverError = new PDOException('Driver failed.');

        self::withEnvironment('development', function () use ($syntheticError, $driverError): void {
            $errors = self::publicResultErrors(new SQLQueryResult([], [$syntheticError, $driverError]));

            self::assertSame([$syntheticError, $driverError], $errors);
        });
    }

    public function testProductionPublicResultErrorsRemoveDriverExceptions(): void
    {
        $syntheticError = new ORMException('General SQL error - Internal server error.');
        $driverError = new PDOException('Driver failed with private connection details.');

        self::withEnvironment('production', function () use ($syntheticError, $driverError): void {
            $errors = self::publicResultErrors(new SQLQueryResult([], [$syntheticError, $driverError]));

            self::assertSame([$syntheticError], $errors);
            self::assertNotContains($driverError, $errors);
        });
    }

    public function testProductionInsertStyleErrorsDoNotExposeDriverExceptions(): void
    {
        $syntheticError = new ORMException('General SQL error - Internal server error.');
        $driverError = new PDOException('Driver failed with private connection details.');

        self::withEnvironment('production', function () use ($syntheticError, $driverError): void {
            $result = new SQLQueryResult([], [$syntheticError, $driverError]);
            $generalSqlError = self::createGeneralSqlQueryException($result);
            $errors = [$generalSqlError, ...self::publicResultErrors($result)];

            self::assertSame([$generalSqlError, $syntheticError], $errors);
            self::assertNotContains($driverError, $errors);
        });
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

    private static function publicResultErrors(SQLQueryResult $result): array
    {
        $reflection = new ReflectionClass(EntityManager::class);
        $entityManager = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('publicResultErrors');
        $errors = $method->invoke($entityManager, $result);

        self::assertIsArray($errors);

        return $errors;
    }

    private static function withEnvironment(string $environment, callable $callback): void
    {
        $hadEnv = array_key_exists('ENV', $_ENV);
        $previousEnv = $_ENV['ENV'] ?? null;
        $hadServerEnv = array_key_exists('ENV', $_SERVER);
        $previousServerEnv = $_SERVER['ENV'] ?? null;

        $_ENV['ENV'] = $environment;
        unset($_SERVER['ENV']);

        try {
            $callback();
        } finally {
            if ($hadEnv) {
                $_ENV['ENV'] = $previousEnv;
            } else {
                unset($_ENV['ENV']);
            }

            if ($hadServerEnv) {
                $_SERVER['ENV'] = $previousServerEnv;
            } else {
                unset($_SERVER['ENV']);
            }
        }
    }
}
