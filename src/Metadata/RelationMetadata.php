<?php

namespace Assegai\Orm\Metadata;

use Assegai\Orm\Enumerations\CascadeOption;
use Assegai\Orm\Enumerations\DeferrableType;
use Assegai\Orm\Enumerations\OnDeleteType;
use Assegai\Orm\Enumerations\OnUpdateType;
use Assegai\Orm\Enumerations\OrphanedRowAction;
use Assegai\Orm\Enumerations\RelationType;
use Closure;

class RelationMetadata
{
  public readonly EntityMetadata $inverseEntityMetadata;
  public readonly RelationType $relationType;
  public readonly Closure|string $target;
  public readonly Closure|string $type;
  public readonly string $propertyName;
  public readonly string $propertyPath;
  public readonly string $joinTableName;
  public readonly string $inverseSidePropertyPath;
  public readonly ?EntityMetadata $junctionEntityMetadata;
  public readonly bool $isTreeParent;
  public readonly bool $isTreeChildren;
  public readonly bool $isPrimary;
  public readonly bool $isLazy;
  public readonly bool $isEager;
  public readonly bool $persistenceEnabled;
  public readonly ?OrphanedRowAction $orphanedRowAction;
  public readonly bool $isCascadeInsert;
  public readonly bool $isCascadeUpdate;
  public readonly bool $isCascadeRemove;
  public readonly bool $isCascadeSoftRemove;
  public readonly bool $isCascadeRecover;
  public readonly bool $isNullable;
  public readonly ?OnDeleteType $onDelete;
  public readonly ?OnUpdateType $onUpdate;
  public readonly ?DeferrableType $deferrable;
  public readonly bool $createForeignKeyConstraints;
  public readonly bool $isOwning;
  public readonly bool $isOneToOne;
  public readonly bool $isOneToOneOwner;
  public readonly bool $isWithJoinColumn;
  public readonly bool $isOneToOneNotOwner;
  public readonly bool $isOneToMany;
  public readonly bool $isManyToOne;
  public readonly bool $isManyToMany;
  public readonly bool $isManyToManyOwner;
  public readonly bool $isManyToManyNotOwner;
  public readonly ?RelationMetadata $inverseRelation;
  public readonly array $foreignKeys;
  public readonly array $joinColumns;
  public readonly array $inverseJoinColumns;
  public readonly ?Closure $givenInverseSidePropertyFactory;

  /**
   * @param EntityMetadata $entityMetadata
   * @param RelationMetadataArgs $args
   * @param EmbeddedMetadata|null $embeddedMetadata
   */
  public function __construct(
    public readonly EntityMetadata $entityMetadata,
    public readonly RelationMetadataArgs $args,
    public readonly ?EmbeddedMetadata $embeddedMetadata = null
  )
  {
    $this->target = $this->args->target;
    $this->propertyName = $this->args->propertyName;
    $this->relationType = $this->args->relationType;

    if ($this->args->inverseSideProperty)
    {
      $this->givenInverseSidePropertyFactory = $this->args->inverseSideProperty;
    }

    $this->isLazy = $this->args->isLazy;

    $this->junctionEntityMetadata = null;
    $this->isPrimary = $this->args->options->isPrimary;

    $this->isCascadeInsert =
      $this->args->options->cascade === true ||
      (
        match(true) {
          is_array($this->args->options->cascade) => in_array('insert', $this->args->options->cascade),
          $this->args->options->cascade instanceof CascadeOption => $this->args->options->cascade === CascadeOption::INSERT,
          default => false
        }
      );

    $this->isCascadeUpdate =
      $this->args->options->cascade === true ||
      (
        match(true) {
          is_array($this->args->options->cascade) => in_array('update', $this->args->options->cascade),
          $this->args->options->cascade instanceof CascadeOption => $this->args->options->cascade === CascadeOption::UPDATE,
          default => false
        }
      );

    $this->isCascadeRemove =
      $this->args->options->cascade === true ||
      (
        match(true) {
          is_array($this->args->options->cascade) => in_array('remove', $this->args->options->cascade),
          $this->args->options->cascade instanceof CascadeOption => $this->args->options->cascade === CascadeOption::REMOVE,
          default => false
        }
      );

    $this->isCascadeSoftRemove =
      $this->args->options->cascade === true ||
      (
        match(true) {
          is_array($this->args->options->cascade) => in_array('soft-remove', $this->args->options->cascade),
          $this->args->options->cascade instanceof CascadeOption => $this->args->options->cascade === CascadeOption::SOFT_REMOVE,
          default => false
        }
      );

    $this->isCascadeRecover =
      $this->args->options->cascade === true ||
      (
        match(true) {
          is_array($this->args->options->cascade) => in_array('recover', $this->args->options->cascade),
          $this->args->options->cascade instanceof CascadeOption => $this->args->options->cascade === CascadeOption::RECOVER,
          default => false
        }
      );

    $this->isNullable = $this->args->options->isNullable === false || !$this->isPrimary;
    $this->onDelete = $this->args->options->onDelete;
    $this->onUpdate = $this->args->options->onUpdate;
    $this->deferrable = $this->args->options->deferrable;
    $this->createForeignKeyConstraints = boolval($this->args->options->createForeignKeyConstraints);
    $this->isEager = boolval($this->args->options->isEager);
    $this->persistenceEnabled = boolval($this->args->options->isPersistent);
    $this->orphanedRowAction = $this->args->options->orphanedRowAction ?? OrphanedRowAction::NULLIFY;

    $this->isTreeParent = $this->args->isTreeParent ?? false;
    $this->isTreeChildren = $this->args->isTreeChildren ?? false;

    # TODO: Resolve type

    $this->isOwning = false;

    $this->isOneToOne = $this->relationType === RelationType::ONE_TO_ONE;
    $this->isOneToOneOwner = false;
    $this->isWithJoinColumn = false;
    $this->isOneToOneNotOwner = $this->isOneToOne;
    $this->isOneToMany = $this->relationType === RelationType::ONE_TO_MANY;
    $this->isManyToOne = $this->relationType === RelationType::MANY_TO_ONE;
    $this->isManyToMany = $this->relationType === RelationType::MANY_TO_MANY;
    $this->isManyToManyOwner = false;
    $this->isManyToManyNotOwner = $this->isManyToMany;
    $this->inverseRelation = null;
    $this->foreignKeys = [];
    $this->joinColumns = [];
    $this->inverseJoinColumns = [];
  }
}