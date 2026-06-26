<?php

namespace Assegai\Orm\Util\TypeConversion;

use Assegai\Orm\Attributes\TypeConverter;
use Assegai\Orm\Exceptions\TypeConversionException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Defines methods for resolving object types.
 */
final class TypeResolver
{
  private static ?TypeResolver $instance = null;

  /**
   * Constructs a new TypeResolver.
   */
  public function __construct()
  {
  }

  /**
   * Returns the singleton instance of the TypeResolver.
   *
   * @return self The singleton instance of the TypeResolver.
   * @deprecated Will be removed in Assegai ORM 0.10.0. Create and inject TypeResolver instead.
   */
  public static function getInstance(): self
  {
    if (!self::$instance) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * @param object $converterHost
   * @param mixed $value
   * @param string $fromType
   * @param string $toType
   * @return mixed
   * @throws ReflectionException
   * @throws TypeConversionException
   */
  public function resolve(object $converterHost, mixed $value, string $fromType, string $toType): mixed
  {
    $resolved = false;
    return $this->tryResolve($converterHost, $value, $fromType, $toType, $resolved);
  }

  /**
   * @param object $converterHost
   * @param mixed $value
   * @param string $fromType
   * @param string $toType
   * @param bool $resolved
   * @return mixed
   * @throws ReflectionException
   * @throws TypeConversionException
   */
  public function tryResolve(object $converterHost, mixed $value, string $fromType, string $toType, bool &$resolved): mixed
  {
    if ( $method =
          $this->findConverter(
            converterHostClassName: $converterHost::class, sourceType: $fromType, targetType: $toType
          ) ) {
      $resolved = true;
      return $method->invokeArgs($converterHost, [$value]);
    }

    $resolved = false;
    return null;
  }

  /**
   * Finds a method whose signature accepts one parameter of the given source type and returns a result of
   * the given target type.
   *
   * @param string $converterHostClassName
   * @param string $sourceType The requited parameter type of the method.
   * @param string $targetType The required return type of the method.
   * @return ReflectionMethod|null Returns a ReflectionMethod if a match is found, otherwise null.
   * @throws ReflectionException
   * @throws TypeConversionException
   */
  public function findConverter(string $converterHostClassName, string $sourceType, string $targetType): ?ReflectionMethod
  {
    $reflectionClass = new ReflectionClass($converterHostClassName);
    $methods = $reflectionClass->getMethods();

    foreach ($methods as $method) {
      if ($this->hasTypeConvertorAttribute($method)) {
        $parameters = $method->getParameters();

        if (empty($parameters)) {
          throw new TypeConversionException(
            "Incorrect parameter count. Type converter methods accept at least 1 parameter."
          );
        }

        foreach ($parameters as $parameter) {
          if ($parameter->getType()->getName() === $sourceType && $method->getReturnType()->getName() === $targetType) {
            return $method;
          }
        }
      }
    }

    return null;
  }

  /**
   * @param ReflectionMethod $method
   * @return bool
   */
  private function hasTypeConvertorAttribute(ReflectionMethod $method): bool
  {
    $attributes = $method->getAttributes(TypeConverter::class);
    return !empty($attributes);
  }
}