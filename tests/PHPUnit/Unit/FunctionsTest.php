<?php

namespace Tests\PHPUnit\Unit;

use PHPUnit\Framework\TestCase;

final class FunctionsTest extends TestCase
{
    public function testArrayHelpers(): void
    {
        $values = [1, 2, 8, 20];
        $greaterThanSeven = fn (int $item): bool => $item > 7;
        $negative = fn (int $item): bool => $item < 0;

        self::assertSame(8, array_find($values, $greaterThanSeven));
        self::assertNull(array_find($values, $negative));
        self::assertNull(array_find([], $greaterThanSeven));

        self::assertSame(20, array_find_last($values, $greaterThanSeven));
        self::assertNull(array_find_last($values, $negative));
        self::assertNull(array_find_last([], $greaterThanSeven));
    }

    public function testObjectHelper(): void
    {
        $object = new class {
            public string $name = 'John Doe';
            public int $age = 30;
        };

        self::assertSame(['name' => 'John Doe', 'age' => 30], object_to_array($object));
    }

    public function testStringHelpers(): void
    {
        $sentence = 'The quick brown fox jumps over the lazy dog';
        $kebab = 'shaka-was-a-zulu';

        self::assertSame('TheQuickBrownFoxJumpsOverTheLazyDog', strtopascal($sentence));
        self::assertSame('ShakaWasAZulu', strtopascal($kebab));

        self::assertSame('theQuickBrownFoxJumpsOverTheLazyDog', strtocamel($sentence));
        self::assertSame('shakaWasAZulu', strtocamel($kebab));

        self::assertSame('the_quick_brown_fox_jumps_over_the_lazy_dog', strtosnake($sentence));
        self::assertSame('shaka_was_a_zulu', strtosnake($kebab));

        self::assertSame('the-quick-brown-fox-jumps-over-the-lazy-dog', strtokebab($sentence));
        self::assertSame($kebab, strtokebab($kebab));
    }
}
