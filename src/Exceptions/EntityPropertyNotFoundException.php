<?php

namespace Assegai\Orm\Exceptions;

use Assegai\Orm\Metadata\EntityMetadata;

class EntityPropertyNotFoundException extends ORMException
{
  public function __construct(string $propertyPath, EntityMetadata $metadata)
  {
    parent::__construct("Property \"$propertyPath\" was not found in \"$metadata->targetName\". Make sure your query is correct.");
  }
}