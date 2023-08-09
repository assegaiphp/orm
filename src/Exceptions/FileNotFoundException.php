<?php

namespace Assegai\Orm\Exceptions;

class FileNotFoundException extends ORMException
{
  public function __construct(string $filename)
  {
    parent::__construct("File not found: $filename");
  }
}