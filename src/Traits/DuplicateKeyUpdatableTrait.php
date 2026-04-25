<?php

namespace Assegai\Orm\Traits;

trait DuplicateKeyUpdatableTrait
{
  public function onDuplicateKeyUpdate(array $assignmentList): static
  {
    $queryString = "";
    if (!empty($assignmentList))
    {
      $queryString .= "ON DUPLICATE KEY UPDATE ";
      foreach ($assignmentList as $assignment)
      {
        $queryString .= "$assignment, ";
      }
    }
    $queryString = rtrim(trim($queryString), ',');
    $this->query->appendQueryString($queryString);
    return $this;
  }
}