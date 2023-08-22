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
 *       Objects       *
 ***********************/

/**
 * Converts an object to an array.
 *
 * @param object $object The object to be converted.
 * @return array The object as an array.
 */
function object_to_array(object $object): array
{
  return json_decode(json_encode($object), true);
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

/**
 * Extracts the class name from a string containing a class definition.
 *
 * @param string $class_definition The input string containing the class definition.
 * @return string|false The class name as a string, or false if the input string does not contain a valid class definition.
 */
function extract_class_name(string $class_definition): string|false
{
  // Attempt to match the class definition using a regular expression.
  if (false === preg_match('/class\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/', $class_definition, $matches))
  {
    // If no match is found, return false.
    return false;
  }

  // Ensure that the $matches array contains at least one element.
  if (count($matches) < 1)
  {
    // If $matches is empty, return false.
    return false;
  }

  // Return the captured class name from the $matches array.
  return $matches[1];
}

/***********************
 *     Directories     *
 ***********************/

/**
 * Empties a directory of all its contents.
 *
 * @param string $directory_path The path to the directory to be emptied.
 * @return bool True if the directory was successfully emptied, false otherwise.
 */
function empty_directory(string $directory_path): bool
{
  if (!is_dir($directory_path))
  {
    return false;
  }

  $items = scandir($directory_path);

  foreach ($items as $item)
  {
    if ($item === '.' || $item === '..')
    {
      continue;
    }

    $path = $directory_path . DIRECTORY_SEPARATOR . $item;

    if (is_dir($path))
    {
      empty_directory($path);
      rmdir($path);
    }
    else
    {
      unlink($path);
    }
  }

  return true;
}
