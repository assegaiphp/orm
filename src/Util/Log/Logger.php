<?php

namespace Assegai\Orm\Util\Log;

use Psr\Log\LoggerInterface;
use Stringable;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Logger - A logger implementation for the ORM.
 *
 * @package Assegai\Orm\Util\Log
 */
class Logger implements LoggerInterface
{
  public function __construct(protected OutputInterface $output)
  {
  }

  /**
   * @inheritDoc
   */
  public function emergency(Stringable|string $message, array $context = []): void
  {
    $this->output->writeln("\e[31m[EMERGENCY]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function alert(Stringable|string $message, array $context = []): void
  {
    $this->output->writeln("\e[31m[ALERT]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function critical(Stringable|string $message, array $context = []): void
  {
    $this->output->writeln("\e[31m[CRITICAL]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function error(Stringable|string $message, array $context = []): void
  {
    $this->output->writeln("\e[31m[ERROR]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function warning(Stringable|string $message, array $context = []): void
  {
    $this->output->writeln("\e[33m[WARNING]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function notice(Stringable|string $message, array $context = []): void
  {
    $this->output->writeln("\e[33m[NOTICE]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function info(Stringable|string $message, array $context = []): void
  {
    $this->output->writeln("\e[34m[INFO]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function debug(Stringable|string $message, array $context = []): void
  {
    $this->output->writeln("\e[2;37m[DEBUG]\e[0m $message");
  }

  /**
   * @inheritDoc
   */
  public function log($level, Stringable|string $message, array $context = []): void
  {
    $this->output->writeln("\e[2;37m[$level]\e[0m $message");
  }
}