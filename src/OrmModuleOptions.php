<?php

namespace Assegai\Orm;

use Closure;

class OrmModuleOptions
{
  public function __construct(
    public readonly ?int     $retryAttempts = 10,
    public readonly ?int     $retryDelay = 3000,
    public readonly ?Closure $toRetry = null,
    public readonly ?bool    $autoLoadEntities = false,
    public readonly ?bool    $verboseRetryLog = false,
  )
  {
  }
}