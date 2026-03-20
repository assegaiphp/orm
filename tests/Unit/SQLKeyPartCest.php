<?php

namespace Tests\Unit;

use Assegai\Orm\Queries\Sql\SQLKeyPart;
use InvalidArgumentException;
use Tests\Support\UnitTester;

class SQLKeyPartCest
{
  public function quotesSafeIdentifiers(UnitTester $I): void
  {
    $keyPart = new SQLKeyPart('users.created_at', ascending: false);

    $I->assertSame('`users`.`created_at` DESC', (string)$keyPart);
  }

  public function rejectsUnsafeIdentifiers(UnitTester $I): void
  {
    $I->expectThrowable(
      new InvalidArgumentException('Unsafe SQL identifier: name; DROP TABLE users'),
      fn() => new SQLKeyPart('name; DROP TABLE users')
    );
  }
}
