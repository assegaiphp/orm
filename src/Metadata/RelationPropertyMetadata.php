<?php

namespace Assegai\Orm\Metadata;

use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Attributes\Relations\JoinColumn;
use Assegai\Orm\Attributes\Relations\JoinTable;
use Assegai\Orm\Attributes\Relations\ManyToMany;
use Assegai\Orm\Attributes\Relations\ManyToOne;
use Assegai\Orm\Attributes\Relations\OneToMany;
use Assegai\Orm\Attributes\Relations\OneToOne;
use Assegai\Orm\Enumerations\RelationType;
use Assegai\Orm\Exceptions\ORMException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

/**
 *
 */
class RelationPropertyMetadata
{
  /**
   * @var string
   */
  public readonly string $name;
  /**
   * @var Entity|null
   */
  private ?Entity $entity;
  public readonly ?string $type;

  /**
   * @param ReflectionProperty $reflectionProperty
   * @param OneToOne|OneToMany|ManyToOne|ManyToMany|null $relationAttribute
   * @param ReflectionAttribute|null $relationAttributeReflection
   * @param JoinColumn|null $joinColumn
   * @param JoinTable|null $joinTable
   * @param ReflectionClass|null $relationReflection
   */
  public function __construct(
    public readonly ReflectionProperty $reflectionProperty,
    public OneToOne|OneToMany|ManyToOne|ManyToMany|null $relationAttribute = null,
    public ?ReflectionAttribute $relationAttributeReflection = null,
    public ?JoinColumn $joinColumn = null,
    public ?JoinTable $joinTable = null,
    public ?ReflectionClass $relationReflection = null,
  )
  {
    $this->name = $this->reflectionProperty->getName();
    $this->type = $this->reflectionProperty->getType()?->getName();
  }

  /**
   * Inflates the metadata.
   */
  public function inflate(): void
  {
    if (!$this->getEntityClass()) {
      return;
    }

    try {
      $reflectionEntity = new ReflectionClass($this->getEntityClass());
      $reflectionEntityAttributes = $reflectionEntity->getAttributes(Entity::class);

      foreach ($reflectionEntityAttributes as $reflectionEntityAttribute) {
        $this->entity = $reflectionEntityAttribute->newInstance();
      }
    } catch (ReflectionException $exception) {
      die(new ORMException(message: $exception->getMessage()));
    }
  }

  /**
   * @return RelationType|null
   */
  public function getRelationType(): ?RelationType
  {
    return match(true) {
      $this->relationAttribute instanceof OneToOne => RelationType::ONE_TO_ONE,
      $this->relationAttribute instanceof OneToMany => RelationType::ONE_TO_MANY,
      $this->relationAttribute instanceof ManyToOne => RelationType::MANY_TO_ONE,
      $this->relationAttribute instanceof ManyToMany => RelationType::MANY_TO_MANY,
      default => null
    };
  }

  /**
   * Gets the raw property type.
   * @return ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType|null
   */
  public function getRawType(): ReflectionIntersectionType|ReflectionNamedType|ReflectionUnionType|null
  {
    return $this->reflectionProperty->getType();
  }

  /**
   * Returns the entity class name.
   *
   * @return string|null
   */
  public function getEntityClass(): ?string
  {
    return $this->relationAttribute?->type;
  }

  /**
   * @return Entity|null
   */
  public function getEntity(): ?Entity
  {
    return $this->entity;
  }
}