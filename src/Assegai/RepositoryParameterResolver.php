<?php

namespace Assegai\Orm\Assegai;

use Assegai\Core\Injector;
use Assegai\Core\Interfaces\ParameterResolverInterface;
use Assegai\Orm\Attributes\InjectRepository;
use ReflectionParameter;

class RepositoryParameterResolver implements ParameterResolverInterface
{
  public function supports(ReflectionParameter $parameter, Injector $injector): bool
  {
    return !empty($parameter->getAttributes(InjectRepository::class));
  }

  public function resolve(ReflectionParameter $parameter, Injector $injector): mixed
  {
    $attribute = $parameter->getAttributes(InjectRepository::class)[0] ?? null;

    if ($attribute === null) {
      return null;
    }

    $attributeInstance = $attribute->newInstance();

    if (method_exists($attributeInstance, 'resolveParameterValue')) {
      return $attributeInstance->resolveParameterValue();
    }

    return $attributeInstance->repository ?? null;
  }
}
