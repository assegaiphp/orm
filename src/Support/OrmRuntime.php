<?php

namespace Assegai\Orm\Support;

final class OrmRuntime
{
  private static array $config = [];
  private static array $moduleConfig = [];

  public static function configure(array $config): void
  {
    self::$config = $config;
  }

  public static function mergeConfig(array $config): void
  {
    self::$config = array_replace_recursive(self::$config, $config);
  }

  public static function setModuleConfig(array $config): void
  {
    self::$moduleConfig = $config;
  }

  public static function mergeModuleConfig(array $config): void
  {
    self::$moduleConfig = array_replace_recursive(self::$moduleConfig, $config);
  }

  public static function config(string $key, mixed $default = null): mixed
  {
    $value = self::getFromArray(self::$config, $key);
    if ($value !== null) {
      return $value;
    }

    $configClass = '\\Assegai\\Core\\Config';
    if (class_exists($configClass) && method_exists($configClass, 'get')) {
      $value = $configClass::get($key);
      if ($value !== null) {
        return $value;
      }
    }

    return $default;
  }

  public static function databaseConfigs(): array
  {
    $databases = self::config('databases', []);
    return is_array($databases) ? $databases : [];
  }

  public static function moduleConfig(string $key, mixed $default = null): mixed
  {
    $value = self::getFromArray(self::$moduleConfig, $key);
    if ($value !== null) {
      return $value;
    }

    $moduleManagerClass = '\\Assegai\\Core\\ModuleManager';
    if (class_exists($moduleManagerClass) && method_exists($moduleManagerClass, 'getInstance')) {
      $moduleManager = $moduleManagerClass::getInstance();
      if ($moduleManager && method_exists($moduleManager, 'getConfig')) {
        $value = $moduleManager->getConfig($key);
        if ($value !== null) {
          return $value;
        }
      }
    }

    return $default;
  }

  public static function defaultPasswordHashAlgorithm(): string|int
  {
    return self::config('default_password_hash_algo', PASSWORD_DEFAULT);
  }

  public static function defaultLimit(): int
  {
    return (int)(self::config('DEFAULT_LIMIT', 10) ?? 10);
  }

  public static function defaultSkip(): int
  {
    return (int)(self::config('DEFAULT_SKIP', 0) ?? 0);
  }

  public static function environment(): string
  {
    $configClass = '\\Assegai\\Core\\Config';
    if (class_exists($configClass) && method_exists($configClass, 'environment')) {
      $environment = $configClass::environment();
      if ($environment instanceof \UnitEnum) {
        return strtoupper($environment->name);
      }

      if (is_string($environment)) {
        return strtoupper($environment);
      }
    }

    $environment = $_ENV['ENV'] ?? $_SERVER['ENV'] ?? 'development';
    return strtoupper((string)$environment);
  }

  public static function isProduction(): bool
  {
    return in_array(self::environment(), ['PROD', 'PRODUCTION'], true);
  }

  public static function log(string $level, string $context, mixed $message): void
  {
    $logClass = '\\Assegai\\Core\\Util\\Debug\\Log';
    if (class_exists($logClass) && method_exists($logClass, $level)) {
      $logClass::$level($context, self::normalizeMessage($message));
      return;
    }

    error_log(sprintf('[%s] %s %s', strtoupper($level), $context, self::normalizeMessage($message)));
  }

  public static function joinPath(string ...$segments): string
  {
    $segments = array_values(array_filter($segments, fn(string $segment): bool => $segment !== ''));

    if (empty($segments)) {
      return '';
    }

    $path = array_shift($segments);
    foreach ($segments as $segment) {
      $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($segment, DIRECTORY_SEPARATOR);
    }

    return $path;
  }

  private static function getFromArray(array $source, string $key): mixed
  {
    if (array_key_exists($key, $source)) {
      return $source[$key];
    }

    $segments = explode('.', $key);
    $value = $source;

    foreach ($segments as $segment) {
      if (!is_array($value) || !array_key_exists($segment, $value)) {
        return null;
      }

      $value = $value[$segment];
    }

    return $value;
  }

  private static function normalizeMessage(mixed $message): string
  {
    if ($message instanceof \Throwable) {
      return $message->getMessage();
    }

    if (is_scalar($message) || $message === null) {
      return (string)$message;
    }

    return json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: 'Unknown ORM runtime message';
  }
}
