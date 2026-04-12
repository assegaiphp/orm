<?php

namespace Tests\SQLite;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Management\Options\FindOneOptions;
use Assegai\Orm\Management\Options\InsertOptions;
use Tests\SQLite\Fixtures\RelationAuthor;
use Tests\SQLite\Fixtures\RelationPost;
use Tests\SQLite\Fixtures\RelationProfile;
use Tests\SQLite\Fixtures\RelationTag;
use Tests\SQLite\Fixtures\RelationUser;
use Tests\Support\UnitTester;

class RelationsCest
{
  private string $dbPath;
  private DataSource $dataSource;
  private EntityManager $manager;

  public function _before(UnitTester $I): void
  {
    require_once __DIR__ . '/Fixtures/RelationFixtures.php';

    $this->dbPath = dirname(__DIR__) . '/_output/sqlite-relations-' . uniqid('', true) . '.sqlite';
    @unlink($this->dbPath);

    $this->dataSource = new DataSource(new DataSourceOptions(
      entities: [
        RelationUser::class,
        RelationProfile::class,
        RelationAuthor::class,
        RelationPost::class,
        RelationTag::class,
      ],
      name: $this->dbPath,
      type: DataSourceType::SQLITE,
    ));

    $this->manager = $this->dataSource->manager;
    $db = $this->dataSource->getClient();

    $db->exec('DROP TABLE IF EXISTS relation_posts_tags');
    $db->exec('DROP TABLE IF EXISTS relation_posts');
    $db->exec('DROP TABLE IF EXISTS relation_tags');
    $db->exec('DROP TABLE IF EXISTS relation_authors');
    $db->exec('DROP TABLE IF EXISTS relation_users');
    $db->exec('DROP TABLE IF EXISTS relation_profiles');

    $db->exec('CREATE TABLE relation_profiles (id INTEGER PRIMARY KEY AUTOINCREMENT, bio TEXT NOT NULL)');
    $db->exec('CREATE TABLE relation_users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, profileId INTEGER)');
    $db->exec('CREATE TABLE relation_authors (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL)');
    $db->exec('CREATE TABLE relation_tags (id INTEGER PRIMARY KEY AUTOINCREMENT, label TEXT NOT NULL)');
    $db->exec('CREATE TABLE relation_posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, author_id INTEGER)');
    $db->exec('CREATE TABLE relation_posts_tags (post_id INTEGER NOT NULL, tag_id INTEGER NOT NULL)');

    $db->exec("INSERT INTO relation_profiles (id, bio) VALUES (1, 'Builder'), (2, 'Explorer')");
    $db->exec("INSERT INTO relation_users (id, name, profileId) VALUES (1, 'Alice', 1), (2, 'Bob', 2)");
    $db->exec("INSERT INTO relation_authors (id, name) VALUES (1, 'John'), (2, 'Mary')");
    $db->exec("INSERT INTO relation_tags (id, label) VALUES (1, 'php'), (2, 'orm'), (3, 'nest')");
    $db->exec("INSERT INTO relation_posts (id, title, author_id) VALUES (1, 'Hello ORM', 1), (2, 'Deep Dive', 1), (3, 'Other Post', 2)");
    $db->exec('INSERT INTO relation_posts_tags (post_id, tag_id) VALUES (1, 1), (1, 2), (2, 2), (2, 3)');
  }

  public function _after(UnitTester $I): void
  {
    unset($this->manager, $this->dataSource);
    @unlink($this->dbPath);
  }

  public function loadsOneToOneRelationsOnOwnerAndInverseSides(UnitTester $I): void
  {
    $user = $this->manager->findOne(
      RelationUser::class,
      new FindOneOptions(where: ['id' => 1], relations: ['profile'])
    )->getData();

    $profile = $this->manager->findOne(
      RelationProfile::class,
      new FindOneOptions(where: ['id' => 1], relations: ['user'])
    )->getData();

    $I->assertSame('Alice', $user->name);
    $I->assertNotNull($user->profile);
    $I->assertSame('Builder', $user->profile->bio);

    $I->assertSame('Builder', $profile->bio);
    $I->assertNotNull($profile->user);
    $I->assertSame('Alice', $profile->user->name);
  }

  public function loadsManyToOneAndOneToManyRelations(UnitTester $I): void
  {
    $post = $this->manager->findOne(
      RelationPost::class,
      new FindOneOptions(where: ['id' => 1], relations: (object)['author' => true])
    )->getData();

    $author = $this->manager->findOne(
      RelationAuthor::class,
      new FindOneOptions(where: ['id' => 1], relations: ['posts'])
    )->getData();

    $I->assertSame('Hello ORM', $post->title);
    $I->assertNotNull($post->author);
    $I->assertSame('John', $post->author->name);

    $I->assertSame('John', $author->name);
    $I->assertCount(2, $author->posts);
    $titles = array_map(fn(object $item) => $item->title, $author->posts);
    sort($titles);
    $I->assertSame(['Deep Dive', 'Hello ORM'], array_values($titles));
  }

  public function loadsManyToManyRelationsOnOwnerAndInverseSides(UnitTester $I): void
  {
    $post = $this->manager->findOne(
      RelationPost::class,
      new FindOneOptions(where: ['id' => 2], relations: ['tags'])
    )->getData();

    $tag = $this->manager->findOne(
      RelationTag::class,
      new FindOneOptions(where: ['id' => 2], relations: ['posts'])
    )->getData();

    $I->assertSame('Deep Dive', $post->title);
    $I->assertCount(2, $post->tags);
    $labels = array_map(fn(object $item) => $item->label, $post->tags);
    sort($labels);
    $I->assertSame(['nest', 'orm'], array_values($labels));

    $I->assertSame('orm', $tag->label);
    $I->assertCount(2, $tag->posts);
    $postTitles = array_map(fn(object $item) => $item->title, $tag->posts);
    sort($postTitles);
    $I->assertSame(['Deep Dive', 'Hello ORM'], array_values($postTitles));
  }

  public function loadsRelationsEvenWhenPrimaryKeyIsExcludedFromPayload(UnitTester $I): void
  {
    $author = $this->manager->findOne(
      RelationAuthor::class,
      new FindOneOptions(where: ['id' => 1], relations: ['posts'], exclude: ['id'])
    )->getData();

    $tag = $this->manager->findOne(
      RelationTag::class,
      new FindOneOptions(where: ['id' => 2], relations: ['posts'], exclude: ['id'])
    )->getData();

    $I->assertFalse(array_key_exists('id', get_object_vars($author)));
    $I->assertCount(2, $author->posts);
    $authorPostTitles = array_map(fn(object $item) => $item->title, $author->posts);
    sort($authorPostTitles);
    $I->assertSame(['Deep Dive', 'Hello ORM'], array_values($authorPostTitles));

    $I->assertFalse(array_key_exists('id', get_object_vars($tag)));
    $I->assertCount(2, $tag->posts);
    $tagPostTitles = array_map(fn(object $item) => $item->title, $tag->posts);
    sort($tagPostTitles);
    $I->assertSame(['Deep Dive', 'Hello ORM'], array_values($tagPostTitles));
  }

  public function insertsRelationObjectsUsingImplicitJoinColumns(UnitTester $I): void
  {
    $author = new RelationAuthor();
    $author->id = 1;
    $author->name = 'John';

    $post = new RelationPost();
    $post->title = 'Inserted With Author';
    $post->author = $author;

    $result = $this->manager->insert(
      RelationPost::class,
      $post,
      new InsertOptions(relations: (object)['author' => true])
    );

    $I->assertTrue($result->isOk());

    $row = $this->dataSource->getClient()
      ->query("SELECT title, author_id FROM relation_posts WHERE title = 'Inserted With Author'")
      ->fetch();

    $I->assertSame('Inserted With Author', $row['title']);
    $I->assertSame(1, (int)$row['author_id']);
  }
}
