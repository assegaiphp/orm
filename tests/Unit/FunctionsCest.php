<?php


namespace Tests\Unit;

use Tests\Support\UnitTester;

class FunctionsCest
{
    public function _before(UnitTester $I): void
    {
    }

    // tests
    public function testArrayFunctions(UnitTester $I): void
    {
      $I->wantToTest("the array_find function");
      $validArrayOfIntegers = [1, 2, 8, 20];
      $itemIsGreaterThanSeverCallback = fn(int $item) => $item > 7;
      $itemIsNegativeCallback = fn(int $item) => $item < 0;

      $I->assertEquals(8, array_find($validArrayOfIntegers, $itemIsGreaterThanSeverCallback));
      $I->assertNull(array_find($validArrayOfIntegers, $itemIsNegativeCallback));
      $I->assertNull(array_find([], $itemIsGreaterThanSeverCallback));

      $I->wantToTest("the array_find_last function");
      $I->assertEquals(20, array_find_last($validArrayOfIntegers, $itemIsGreaterThanSeverCallback));
      $I->assertNull(array_find_last($validArrayOfIntegers, $itemIsNegativeCallback));
      $I->assertNull(array_find_last([], $itemIsGreaterThanSeverCallback));
    }

    public function testObjectFunctions(UnitTester $I): void
    {
      $I->wantToTest("the object_to_array function");
      $validObject = new class {
        public string $name = "John Doe";
        public int $age = 30;
      };

      $I->assertEquals(["name" => "John Doe", "age" => 30], object_to_array($validObject));
    }

    public function testStringFunctions(UnitTester $I): void
    {
      $validString = "The quick brown fox jumps over the lazy dog";
      $validKebabCase = 'shaka-was-a-zulu';

      $I->wantToTest("the strtopascal function");
      $I->assertEquals("TheQuickBrownFoxJumpsOverTheLazyDog", strtopascal($validString));
      $I->assertEquals("ShakaWasAZulu", strtopascal($validKebabCase));

      $I->wantToTest("the strtocamel function");
      $I->assertEquals("theQuickBrownFoxJumpsOverTheLazyDog", strtocamel($validString));
      $I->assertEquals("shakaWasAZulu", strtocamel($validKebabCase));

      $I->wantToTest("the strtosnake function");
      $I->assertEquals("the_quick_brown_fox_jumps_over_the_lazy_dog", strtosnake($validString));
      $I->assertEquals("shaka_was_a_zulu", strtosnake($validKebabCase));

      $I->wantToTest("the strtokebab function");
      $I->assertEquals("the-quick-brown-fox-jumps-over-the-lazy-dog", strtokebab($validString));
      $I->assertEquals($validKebabCase, strtokebab($validKebabCase));
    }
}
