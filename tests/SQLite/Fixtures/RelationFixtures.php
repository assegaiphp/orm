<?php

namespace Tests\SQLite\Fixtures;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\PasswordColumn;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Attributes\Relations\JoinColumn;
use Assegai\Orm\Attributes\Relations\JoinTable;
use Assegai\Orm\Attributes\Relations\ManyToMany;
use Assegai\Orm\Attributes\Relations\ManyToOne;
use Assegai\Orm\Attributes\Relations\OneToMany;
use Assegai\Orm\Attributes\Relations\OneToOne;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Queries\Sql\ColumnType;

#[Entity(table: 'relation_profiles', database: 'relation-test', driver: DataSourceType::SQLITE)]
class RelationProfile
{
  #[PrimaryGeneratedColumn]
  public ?int $id = null;

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $bio = '';

  #[OneToOne(type: RelationUser::class)]
  public ?RelationUser $user = null;
}

#[Entity(table: 'relation_users', database: 'relation-test', driver: DataSourceType::SQLITE)]
class RelationUser
{
  #[PrimaryGeneratedColumn]
  public ?int $id = null;

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $name = '';

  #[OneToOne(type: RelationProfile::class)]
  #[JoinColumn(name: 'profileId')]
  public ?RelationProfile $profile = null;
}

#[Entity(table: 'relation_authors', database: 'relation-test', driver: DataSourceType::SQLITE)]
class RelationAuthor
{
  #[PrimaryGeneratedColumn]
  public ?int $id = null;

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $name = '';

  #[OneToMany(type: RelationPost::class)]
  public array $posts = [];
}

#[Entity(table: 'relation_tags', database: 'relation-test', driver: DataSourceType::SQLITE)]
class RelationTag
{
  #[PrimaryGeneratedColumn]
  public ?int $id = null;

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $label = '';

  #[PasswordColumn(name: 'credential_hash')]
  public string $credentialHash = '';

  #[Column(name: 'password', type: ColumnType::VARCHAR, nullable: false)]
  public string $legacyCredential = '';

  #[ManyToMany(type: RelationPost::class, inverseSide: 'tags')]
  public array $posts = [];
}

#[Entity(table: 'relation_posts', database: 'relation-test', driver: DataSourceType::SQLITE)]
class RelationPost
{
  #[PrimaryGeneratedColumn]
  public ?int $id = null;

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $title = '';

  #[ManyToOne(type: RelationAuthor::class)]
  #[JoinColumn(name: 'author_id', referencedColumnName: 'id')]
  public ?RelationAuthor $author = null;

  #[ManyToMany(type: RelationTag::class, inverseSide: 'posts')]
  #[JoinTable(name: 'relation_posts_tags', joinColumn: 'post_id', inverseJoinColumn: 'tag_id')]
  public array $tags = [];
}

#[Entity(table: 'relation_publishers', database: 'relation-test', driver: DataSourceType::SQLITE)]
class RelationPublisher
{
  #[PrimaryGeneratedColumn]
  public ?int $id = null;

  #[Column(alias: 'publicCode', type: ColumnType::VARCHAR, nullable: false)]
  public string $code = '';

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $name = '';

  #[OneToMany(type: RelationIssue::class)]
  public array $issues = [];
}

#[Entity(table: 'relation_issues', database: 'relation-test', driver: DataSourceType::SQLITE)]
class RelationIssue
{
  #[PrimaryGeneratedColumn]
  public ?int $id = null;

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $title = '';

  #[ManyToOne(type: RelationPublisher::class)]
  #[JoinColumn(name: 'publisher_code', referencedColumnName: 'code')]
  public ?RelationPublisher $publisher = null;
}

#[Entity(table: 'relation_legacy_parents', database: 'relation-test', driver: DataSourceType::SQLITE)]
class RelationLegacyParent
{
  #[PrimaryGeneratedColumn]
  public ?int $id = null;

  #[Column(alias: 'publicUuid', type: ColumnType::VARCHAR, nullable: false)]
  public string $uuid = '';

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $name = '';

  #[OneToMany(RelationLegacyChild::class, 'uuid', 'parent')]
  public array $children = [];
}

#[Entity(table: 'relation_legacy_children', database: 'relation-test', driver: DataSourceType::SQLITE)]
class RelationLegacyChild
{
  #[PrimaryGeneratedColumn]
  public ?int $id = null;

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $label = '';

  #[ManyToOne(type: RelationLegacyParent::class)]
  #[JoinColumn(name: 'parent_uuid')]
  public ?RelationLegacyParent $parent = null;
}
