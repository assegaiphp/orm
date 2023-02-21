<?php

/***********************
 *        Arrays       *
 ***********************/

/**
 * Returns the first element in the provided array that satisfies the provided testing function.
 *
 * @param array $arr The array to search through.
 * @param callable $callback A callable function that accepts an element of $arr and returns a boolean value.
 *
 * @return mixed|null The first element in $arr that satisfies $callback. Returns null if no element satisfies the
 * condition.
 */
function array_find(array $arr, callable $callback): mixed
{
  foreach ($arr as $item)
  {
    if ($callback($item))
    {
      return $item;
    }
  }
  return null;
}

/**
 * Iterates the given array in reverse order and returns the value of the first element that satisfies the provided
 * testing function. If no elements satisfy the testing function, null is returned.
 *
 * @param array $arr The array to search through.
 * @param callable $callback A callable function that accepts an element of $arr and returns a boolean value.
 *
 * @return mixed|null The last element in $arr that satisfies $callback. Returns null if no element satisfies the
 * condition.
 */
function array_find_last(array $arr, callable $callback): mixed
{
  $reversedArray = array_reverse($arr);
  return array_find($reversedArray, $callback);
}