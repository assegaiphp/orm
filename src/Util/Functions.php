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

/***********************
 *       Strings       *
 ***********************/

/**
 * Converts a string to Pascal Case format.
 *
 * @param string $string The string to be converted.
 *
 * @return string The string in Pascal Case format.
 */
function strtopascal(string $string): string
{
  $words = preg_split('/[\s\-\W_]+/', $string);
  $words = array_map(fn($word) => ucfirst($word), $words);

  return implode('', $words);
}

/**
 * Converts a string to Camel Case format.
 *
 * @param string $string The string to be converted.
 *
 * @return string The string in Camel Case format
 */
function strtocamel(string $string): string
{
  return lcfirst(strtopascal($string));
}

/**
 * Converts a string to snake_case.
 *
 * @param string $name The string to be converted.
 *
 * @return string The string in snake_case.
 */
function strtosnake(string $string): string
{
  return mb_strtolower(preg_replace('/[\s\-\W]+/', '_', $string));
}

/**
 * Converts a string to Kebab Case.
 *
 * @param string $string The string to be converted.
 *
 * @return string The string in Kebab Case.
 */
function strtokebab(string $string): string
{
  return mb_strtolower(preg_replace('/[\s_\W]+/', '-', $string));
}