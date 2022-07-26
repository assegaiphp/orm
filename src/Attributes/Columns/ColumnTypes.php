<?php

$types = [];
$workingDirectory = scandir(__DIR__);
$columnClassFileNames = array_slice($workingDirectory, 2);

foreach ($columnClassFileNames as $filename)
{
  if (str_ends_with($filename, 'Column.php'))
  {
    $className = substr($filename, 0, -4);
    $types[] = "Assegaiphp\\Orm\\Attributes\\Columns\\${className}";
  }
}

return $types;
