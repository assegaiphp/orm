<?php

namespace Assegai\Orm\Relations;

use Assegai\Orm\Enumerations\RelationType;

final readonly class RelationBuildPlan
{
  public function __construct(
    public string           $relationName,
    public RelationType     $relationType,
    public RelationOptions  $joinColumnName = new RelationOptions(),
  )
  {
  }
}