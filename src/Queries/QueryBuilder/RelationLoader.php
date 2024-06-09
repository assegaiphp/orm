<?php

namespace Assegai\Orm\Queries\QueryBuilder;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Metadata\RelationMetadata;
use Assegai\Orm\Queries\Sql\SQLQuery;

/**
 * Class RelationLoader. Loads relation data for entities.
 *
 * @package Assegai\Orm\Queries\QueryBuilder
 */
readonly class RelationLoader
{
  /**
   * RelationLoader constructor.
   *
   * @param DataSource $connection
   */
  public function __construct(private DataSource $connection)
  {
  }

  /**
   * Loads relation data for the given entity and its relation.
   *
   * @param RelationMetadata $relation
   * @param array|object $entityOrEntities
   * @param mixed|null $queryRunner
   * @param mixed|null $selectQueryBuilder
   * @return mixed
   */
  public function load(
    RelationMetadata $relation,
    array|object $entityOrEntities,
    mixed $queryRunner = null,
    mixed $selectQueryBuilder = null
  ): mixed
  {
    if ($queryRunner && $queryRunner->isReleased)
    {
      // Get a new one if already closed
      $queryRunner = null;
    }

    return match(true) {
      $relation->isManyToOne,
      $relation->isOneToOneOwner => $this->loadManyToOneOrOneToOneOwner(
        relation: $relation,
        entityOrEntities: $entityOrEntities,
        queryRunner: $queryRunner,
        selectQueryBuilder: $selectQueryBuilder
      ),
      $relation->isOneToMany,
      $relation->isOneToOneNotOwner => $this->loadOneToManyOrOneToOneNotOwner(
        relation: $relation,
        entityOrEntities: $entityOrEntities,
        queryRunner: $queryRunner,
        selectQueryBuilder: $selectQueryBuilder
      ),
      $relation->isManyToManyOwner => $this->loadManyToManyOwner(
        relation: $relation,
        entityOrEntities: $entityOrEntities,
        queryRunner: $queryRunner,
        selectQueryBuilder: $selectQueryBuilder
      ),
      default => $this->loadManyToManyNotOwner(
        relation: $relation,
        entityOrEntities: $entityOrEntities,
        queryRunner: $queryRunner,
        selectQueryBuilder: $selectQueryBuilder
      )
    };
  }

  /**
   * Loads data for many-to-one and one-to-one owner relations.
   *
   * (ow) post.category<=>category.post
   * loaded: category from post
   * example: SELECT category.id AS category_id, category.name as category_name FROM category category
   *            INNER JOIN post Post ON Post.category=category.id WHERE Post.id=1
   * @param RelationMetadata $relation
   * @param array|object $entityOrEntities
   * @param mixed|null $queryRunner
   * @param mixed|null $selectQueryBuilder
   * @return mixed
   */
  public function loadManyToOneOrOneToOneOwner(
    RelationMetadata $relation,
    array|object $entityOrEntities,
    mixed $queryRunner = null,
    ?SQLQuery $selectQueryBuilder = null
  ): mixed {
    // TODO: Implement loadManyToOneOrOneToOneOwner() method.
    $entities = is_array($entityOrEntities) ? $entityOrEntities : [$entityOrEntities];
    $joinAliasName = $relation->entityMetadata->name;
    $query = $selectQueryBuilder ?? new SQLQuery(db: $this->connection->getClient());
    return $this->connection;
  }

  public function loadOneToManyOrOneToOneNotOwner(
    RelationMetadata $relation,
    array|object $entityOrEntities,
    mixed $queryRunner = null,
    mixed $selectQueryBuilder = null
  ): mixed {
    return $this->connection;
  }

  public function loadManyToManyOwner(
    RelationMetadata $relation,
    array|object $entityOrEntities,
    mixed $queryRunner = null,
    mixed $selectQueryBuilder = null
  ): mixed {
    return $this->connection;
  }

  /**
   * Loads data for many-to-many not owner relations.
   *
   * SELECT post
   * FROM post post
   * INNER JOIN post_categories post_categories
   * ON post_categories.postId = post.id
   * AND post_categories.categoryId = post_categories.categoryId
   *
   * @param mixed $relation
   * @param array|object $entityOrEntities
   * @param mixed|null $queryRunner
   * @param mixed|null $selectQueryBuilder
   * @return mixed
   */
  public function loadManyToManyNotOwner(
    mixed $relation,
    array|object $entityOrEntities,
    mixed $queryRunner = null,
    mixed $selectQueryBuilder = null
  ): mixed {
    return $this->connection;
  }
}