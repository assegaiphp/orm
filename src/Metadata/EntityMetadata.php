<?php

namespace Assegai\Orm\Metadata;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Enumerations\InheritancePattern;
use Assegai\Orm\Enumerations\TableType;
use Assegai\Orm\Management\Options\OrderByCondition;
use Closure;

/**
 * Contains all entity metadata
 */
class EntityMetadata
{
  /**
   * @var TableMetadataArgs $tableMetadataArgs Metadata arguments used to build this entity metadata.
   */
  public readonly TableMetadataArgs $tableMetadataArgs;
  /**
   * @var EntityMetadata $closureJunctionTable
   */
  public readonly EntityMetadata $closureJunctionTable;
  /**
   * @var EntityMetadata $parentEntityMetadata
   */
  public readonly EntityMetadata $parentEntityMetadata;
  /**
   * @var EntityMetadata[] $childEntityMetadata
   */
  public readonly array $childEntityMetadata;
  public readonly TableType $tableType;
  /**
   * @var Closure|string $target Target class to which this entity metadata is bind.
   */
  public readonly Closure|string $target;
  public readonly string $targetName;
  public readonly string $name;
  public readonly ?string $expression;
  public readonly ?array $dependsOn;
  public readonly ?bool $withoutRowId;
  public readonly ?string $givenTableName;
  public readonly string $tableName;
  public readonly string $tablePath;
  public readonly string $tableNameWithoutPrefix;
  public readonly bool $synchronize;
  public readonly ?string $engine;
  public readonly ?string $database;
  public readonly ?string $schema;
  /**
   * @var OrderByCondition[]|null $orderBy Specifies a default order by used for
   * queries from this table when no explicit order by is specified.
   */
  public readonly ?array $orderBy;
  public readonly bool $hasNonNullableRelations;
  public readonly bool $isJunction;
  public readonly bool $isAlwaysUsingConstructor;
  public readonly mixed $treeType;
  public readonly mixed $treeOptions;
  public readonly bool $isClosureJunction;
  public readonly bool $hasMultiplePrimaryKeys;
  public readonly bool $hasUUIDGeneratedColumns;
  public readonly ?string $discriminatorValue;
  /**
   * @var ColumnMetadata[] $ownColumns
   */
  public readonly array $ownColumns;
  /**
   * @var ColumnMetadata[]
   */
  public readonly array $ancestorColumns;
  /**
   * @var ColumnMetadata[]
   */
  public readonly array $descendantColumns;
  /**
   * @var ColumnMetadata[]
   */
  public readonly array $nonVirtualColumns;
  /**
   * @var ColumnMetadata[]
   */
  public readonly array $ownerColumns;
  /**
   * @var ColumnMetadata[]
   */
  public readonly array $inverseColumns;
  /**
   * @var ColumnMetadata[]
   */
  public readonly array $generatedColumns;
  public readonly ?ColumnMetadata $objectIdColumn;
  public readonly ?ColumnMetadata $createDateColumn;
  public readonly ?ColumnMetadata $updateDateColumn;
  public readonly ?ColumnMetadata $deleteDateColumn;
  public readonly ?ColumnMetadata $versionColumn;
  public readonly ?ColumnMetadata $discriminatorColumn;
  public readonly ?ColumnMetadata $treeLevelColumn;
  public readonly ?ColumnMetadata $nestedSetLeftColumn;
  public readonly ?ColumnMetadata $nestedSetRightColum;
  public readonly ?ColumnMetadata $materializedPathColumn;
  /**
   * @var ColumnMetadata[]
   */
  public readonly array $primaryColumns;
  /**
   * @var RelationMetadata[]
   */
  public readonly array $ownRelations;
  /**
   * @var RelationMetadata[]
   */
  public readonly array $relations;
  /**
   * @var RelationMetadata[]
   */
  public readonly array $eagerRelations;
  /**
   * @var RelationMetadata[]
   */
  public readonly array $lazyRelations;
  /**
   * @var RelationMetadata[]
   */
  public readonly array $oneToOneRelations;
  /**
   * @var RelationMetadata[]
   */
  public readonly array $ownerOneToOneRelations;
  /**
   * @var RelationMetadata[]
   */
  public readonly array $oneToManyRelations;
  /**
   * @var RelationMetadata[]
   */
  public readonly array $manyToOneRelations;
  /**
   * @var RelationMetadata[]
   */
  public readonly array $manyToManyRelations;
  /**
   * @var RelationMetadata[]
   */
  public readonly array $ownerManyToManyRelations;
  /**
   * @var RelationMetadata[]
   */
  public readonly array $relationsWithJoinColumns;
  public readonly ?RelationMetadata $treeParentRelation;
  public readonly ?RelationMetadata $treeChildrenRelation;
  /**
   * @var RelationMetadata[]
   */
  public readonly array $relationIds;
  public readonly array $relationCounts;
  public readonly array $foreignKeys;
  public readonly array $allEmbeddeds;

  /**
   * @param DataSource $connection
   * @param TableMetadataArgs $args
   * @param array|null $inheritanceTree
   * @param InheritancePattern|null $inheritancePattern
   * @param EntityMetadata|null $parentClosureEntityMetadata
   */
  public function __construct(
    public readonly DataSource $connection,
    public readonly TableMetadataArgs $args,
    public readonly ?array $inheritanceTree = null,
    public readonly ?InheritancePattern $inheritancePattern = null,
    public readonly ?EntityMetadata $parentClosureEntityMetadata = null,
  )
  {
    $this->childEntityMetadata = [];
    $this->tableType = $this->args->type;
    $this->synchronize = $this->args->synchronize ?? true;
    $this->hasNonNullableRelations = false;
  }

  public function findEmbeddedWithPropertyPath(string $propertyPath): ?EmbeddedMetadata
  {
    foreach ($this->allEmbeddeds as $embedded)
    {
      // TODO implement the findEmbeddedWithPropertyPath() method.
    }
    return null;
  }
}