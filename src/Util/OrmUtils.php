<?php

namespace Assegai\Orm\Util;

class OrmUtils
{
  public static function propertyPathsToTruthyObject(array $paths): object
  {
    return (object)$paths;
  }
}