<?php

namespace Tests\Unit;

use Assegai\Orm\Management\Options\FindOptions;
use stdClass;
use Tests\Support\UnitTester;

class FindOptionsCest
{
  public function testTheConstructorAcceptsObjectRelations(UnitTester $I): void
  {
    $relations = new stdClass();
    $relations->{' profile '} = true;
    $relations->posts = false;

    $options = new FindOptions(relations: $relations);

    $I->assertIsArray($options->relations);
    $I->assertSame(['profile' => true, 'posts' => false], $options->relations);
  }
}
