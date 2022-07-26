<?php

namespace Assegai\Orm\Util;

use Assegai\Orm\Attributes\Columns\CreateDateColumn;
use Assegai\Orm\Attributes\Columns\DeleteDateColumn;
use Assegai\Orm\Attributes\Columns\UpdateDateColumn;
use Assegai\Orm\Attributes\Entity;
use ReflectionClass;
use ReflectionProperty;

class Filter
{
  public static function getCreateDateColumnName(Entity $entity): string
  {
    $instance = new ReflectionClass($entity);
    $properties = $instance->getProperties(ReflectionProperty::IS_PUBLIC);

    $name = '';

    foreach ($properties as $prop)
    {
      if (in_array($prop->getName(), ['createdAt', 'created_at']))
      {
        $name = $prop->getName();

        $attributes = $prop->getAttributes(name: CreateDateColumn::class);

        foreach ($attributes as $columnAttribute)
        {
          $columnAttributeInstance = $columnAttribute->newInstance();
          if (!empty($columnAttributeInstance->name))
          {
            $name = $columnAttributeInstance->name;
          }
        }
      }
    }

    return $name;
  }

  public static function getUpdateDateColumnName(Entity $entity): string
  {
    $instance = new ReflectionClass($entity);
    $properties = $instance->getProperties(ReflectionProperty::IS_PUBLIC);

    $name = '';

    foreach ($properties as $prop)
    {
      if (in_array($prop->getName(), ['updatedAt', 'updated_at']))
      {
        $name = $prop->getName();

        $attributes = $prop->getAttributes(name: UpdateDateColumn::class);

        foreach ($attributes as $columnAttribute)
        {
          $columnAttributeInstance = $columnAttribute->newInstance();
          if (!empty($columnAttributeInstance->name))
          {
            $name = $columnAttributeInstance->name;
          }
        }
      }
    }

    return $name;
  }

  public static function getDeleteDateColumnName(object $entity): string
  {
    $instance = new ReflectionClass($entity);
    $properties = $instance->getProperties(ReflectionProperty::IS_PUBLIC);

    $name = '';

    foreach ($properties as $prop) {
      if (in_array($prop->getName(), ['deletedAt', 'deleted_at'])) {
        $name = $prop->getName();

        $attributes = $prop->getAttributes(name: DeleteDateColumn::class);

        foreach ($attributes as $columnAttribute) {
          $columnAttributeInstance = $columnAttribute->newInstance();
          if (!empty($columnAttributeInstance->name)) {
            $name = $columnAttributeInstance->name;
          }
        }
      }
    }

    return $name;
  }
}