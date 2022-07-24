<?php

namespace Assegai\Orm\Traits;

use Assegai\Orm\Queries\Sql\SQLInsertIntoMultipleStatement;
use Assegai\Orm\Queries\Sql\SQLInsertIntoStatement;

trait DuplicateKeyUpdatableTrait
{
  public function onDuplicateKeyUpdate(array $assignmentList): SQLInsertIntoStatement|SQLInsertIntoMultipleStatement
  {
    $queryString = "";
    if (!empty($assignmentList))
    {
      $queryString .= "ON DUPLICATE KEY UPDATE ";
      foreach ($assignmentList as $assignment)
      {
        $queryString .= "$assignment ";
      }
    }
    $queryString = trim($queryString);
    $this->query->appendQueryString($queryString);
    return $this;
  }
}