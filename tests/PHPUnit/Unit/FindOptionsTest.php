<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Management\Options\FindOptions;
use PHPUnit\Framework\TestCase;
use stdClass;

final class FindOptionsTest extends TestCase
{
    public function testConstructorAcceptsObjectRelations(): void
    {
        $relations = new stdClass();
        $relations->{' profile '} = true;
        $relations->posts = false;

        $options = new FindOptions(relations: $relations);

        self::assertIsArray($options->relations);
        self::assertSame(['profile' => true, 'posts' => false], $options->relations);
    }
}
