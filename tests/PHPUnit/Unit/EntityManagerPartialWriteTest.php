<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Attributes\Relations\JoinColumn;
use Assegai\Orm\Attributes\Relations\ManyToOne;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Management\Options\InsertOptions;
use Assegai\Orm\Management\Options\UpdateOptions;
use Assegai\Orm\Management\Repository;
use Assegai\Orm\Queries\Sql\ColumnType;
use Assegai\Orm\Queries\Sql\SQLQuery;
use PDO;
use PHPUnit\Framework\TestCase;

final class EntityManagerPartialWriteTest extends TestCase
{
    private ?DataSource $dataSource = null;
    private string $databasePath;

    protected function setUp(): void
    {
        $this->databasePath = sys_get_temp_dir() . '/assegai-partial-write-' . uniqid('', true) . '.sqlite';
        $this->cleanupSqliteFiles($this->databasePath);
        $this->dataSource = new DataSource(new DataSourceOptions(
            entities: [
                NullableCatalogItemEntity::class,
                CatalogCategoryEntity::class,
                CatalogListingEntity::class,
                CatalogListingWithScalarEntity::class,
            ],
            name: $this->databasePath,
            type: DataSourceType::SQLITE,
        ));

        $this->dataSource->getClient()->exec(
            'CREATE TABLE nullable_catalog_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                description TEXT NULL
            )'
        );
        $this->dataSource->getClient()->exec(
            'CREATE TABLE catalog_categories (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL
            )'
        );
        $this->dataSource->getClient()->exec(
            'CREATE TABLE catalog_listings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                category_id INTEGER NULL
            )'
        );
    }

    protected function tearDown(): void
    {
        $this->dataSource?->disconnect();
        $this->cleanupSqliteFiles($this->databasePath);
    }

    public function testArrayPartialUpdateWritesExplicitNull(): void
    {
        $this->insertCatalogItem('Widget', 'Ready to clear');

        $result = $this->createManager(NullableCatalogItemEntity::class)->update(
            NullableCatalogItemEntity::class,
            ['description' => null],
            ['name' => 'Widget'],
        );

        self::assertTrue($result->isOk());
        self::assertNull($this->fetchCatalogItem('Widget')['description']);
    }

    public function testObjectPartialUpdateSkipsDefaultNullsUnlessRequested(): void
    {
        $this->insertCatalogItem('Gadget', 'Keep this value');

        $payload = new NullableCatalogItemPatch();
        $result = $this->createManager(NullableCatalogItemEntity::class)->update(
            NullableCatalogItemEntity::class,
            $payload,
            ['name' => 'Gadget'],
        );

        self::assertSame(0, $result->affected);
        self::assertSame('Keep this value', $this->fetchCatalogItem('Gadget')['description']);

        $result = $this->createManager(NullableCatalogItemEntity::class)->update(
            NullableCatalogItemEntity::class,
            $payload,
            ['name' => 'Gadget'],
            new UpdateOptions(writeNulls: ['description']),
        );

        self::assertTrue($result->isOk());
        self::assertNull($this->fetchCatalogItem('Gadget')['description']);
    }

    public function testRelationIdAliasUpdatesJoinColumnWithoutPublicScalarProperty(): void
    {
        $this->insertCategory(42, 'Hardware');
        $this->insertCatalogListing('Cordless Drill', null);

        $payload = new CatalogListingRelationPatch();
        $payload->categoryId = 42;

        $result = $this->createManager(CatalogListingEntity::class)->update(
            CatalogListingEntity::class,
            $payload,
            ['name' => 'Cordless Drill'],
        );

        self::assertTrue($result->isOk());
        self::assertSame(42, $this->fetchCatalogListing('Cordless Drill')['category_id']);
        self::assertObjectNotHasProperty('categoryId', $result->generatedMaps);
    }

    public function testArrayPayloadIdStaysReadonlyWhenRelationIdAliasExists(): void
    {
        $this->insertCategory(42, 'Hardware');
        $this->insertCatalogListing('Bench Vise', 42);
        $original = $this->fetchCatalogListing('Bench Vise');

        $result = $this->createManager(CatalogListingEntity::class)->update(
            CatalogListingEntity::class,
            ['id' => 999],
            ['name' => 'Bench Vise'],
        );

        $updated = $this->fetchCatalogListing('Bench Vise');
        self::assertSame(0, $result->affected);
        self::assertSame($original['id'], $updated['id']);
        self::assertFalse($this->catalogListingExistsById(999));
    }

    public function testObjectPayloadIdStaysReadonlyWhenRelationIdAliasExists(): void
    {
        $this->insertCategory(42, 'Hardware');
        $this->insertCatalogListing('Table Saw', 42);
        $original = $this->fetchCatalogListing('Table Saw');

        $result = $this->createManager(CatalogListingEntity::class)->update(
            CatalogListingEntity::class,
            new CatalogListingReadonlyIdPatch(),
            ['name' => 'Table Saw'],
        );

        $updated = $this->fetchCatalogListing('Table Saw');
        self::assertSame(0, $result->affected);
        self::assertSame($original['id'], $updated['id']);
        self::assertFalse($this->catalogListingExistsById(999));
    }

    public function testRelationIdAliasClearRequiresObjectNullWriteOptIn(): void
    {
        $this->insertCategory(42, 'Hardware');
        $this->insertCatalogListing('Workbench', 42);

        $payload = new CatalogListingRelationPatch();
        $result = $this->createManager(CatalogListingEntity::class)->update(
            CatalogListingEntity::class,
            $payload,
            ['name' => 'Workbench'],
        );

        self::assertSame(0, $result->affected);
        self::assertSame(42, $this->fetchCatalogListing('Workbench')['category_id']);

        $result = $this->createManager(CatalogListingEntity::class)->update(
            CatalogListingEntity::class,
            $payload,
            ['name' => 'Workbench'],
            new UpdateOptions(writeNulls: ['categoryId']),
        );

        self::assertTrue($result->isOk());
        self::assertNull($this->fetchCatalogListing('Workbench')['category_id']);
        self::assertObjectNotHasProperty('categoryId', $result->generatedMaps);
    }

    public function testRelationIdAliasClearHonorsColumnNameWriteNullOptIn(): void
    {
        $this->insertCategory(42, 'Hardware');
        $this->insertCatalogListing('Column Opt In Workbench', 42);

        $payload = new CatalogListingRelationPatch();
        $result = $this->createManager(CatalogListingEntity::class)->update(
            CatalogListingEntity::class,
            $payload,
            ['name' => 'Column Opt In Workbench'],
            new UpdateOptions(writeNulls: ['category_id']),
        );

        self::assertTrue($result->isOk());
        self::assertNull($this->fetchCatalogListing('Column Opt In Workbench')['category_id']);
        self::assertObjectNotHasProperty('categoryId', $result->generatedMaps);
    }

    public function testRepositoryInsertAcceptsRelationIdAliasWithoutPublicScalarProperty(): void
    {
        $this->insertCategory(42, 'Hardware');

        $payload = new CatalogListingCreatePayload();
        $payload->name = 'Cordless Sander';
        $payload->categoryId = 42;

        $repository = new Repository(CatalogListingEntity::class, $this->createManager(CatalogListingEntity::class));
        $result = $repository->insert($payload);

        self::assertTrue($result->isOk());
        self::assertSame(1, substr_count($result->getRaw(), '"category_id"'));
        self::assertSame(42, $this->fetchCatalogListing('Cordless Sander')['category_id']);
        self::assertObjectNotHasProperty('categoryId', $result->generatedMaps);
    }

    public function testInsertRejectsListArrayRows(): void
    {
        $result = $this->createManager(NullableCatalogItemEntity::class)->insert(
            NullableCatalogItemEntity::class,
            [['name' => 'Bulk Widget']],
        );

        self::assertTrue($result->isError());
        self::assertInstanceOf(ORMException::class, $result->getErrors()[0] ?? null);
        self::assertFalse($this->catalogItemExistsByName('Bulk Widget'));
    }

    public function testInsertRejectsMixedNumericKeyPayload(): void
    {
        $result = $this->createManager(NullableCatalogItemEntity::class)->insert(
            NullableCatalogItemEntity::class,
            ['name' => 'Numeric Key Item', 0 => 'ignored'],
        );

        self::assertTrue($result->isError());
        self::assertInstanceOf(ORMException::class, $result->getErrors()[0] ?? null);
        self::assertFalse($this->catalogItemExistsByName('Numeric Key Item'));
    }

    public function testSqliteUpsertAcceptsRelationIdAliasWithoutPublicScalarProperty(): void
    {
        $this->insertCategory(42, 'Hardware');

        $payload = new CatalogListingCreatePayload();
        $payload->name = 'Cordless Router';
        $payload->categoryId = 42;

        $result = $this->createManager(CatalogListingEntity::class)->upsert(
            CatalogListingEntity::class,
            $payload,
            ['name'],
        );

        self::assertTrue($result->isOk());
        self::assertStringContainsString('"category_id"', $result->getRaw());
        self::assertSame(42, $this->fetchCatalogListing('Cordless Router')['category_id']);
        self::assertObjectNotHasProperty('categoryId', $result->generatedMaps);
    }

    public function testUpdateReadbackNormalizesRelationIdAliasConditions(): void
    {
        $this->insertCategory(42, 'Hardware');
        $this->insertCatalogListing('Cordless Jigsaw', 42);

        $result = $this->createManager(CatalogListingEntity::class)->update(
            CatalogListingEntity::class,
            ['name' => 'Cordless Jigsaw Kit'],
            ['categoryId' => 42],
        );

        self::assertTrue($result->isOk());
        self::assertSame('Cordless Jigsaw Kit', $result->generatedMaps->name ?? null);
        self::assertSame(42, $this->fetchCatalogListing('Cordless Jigsaw Kit')['category_id']);
    }

    public function testUpsertConflictPathsResolveRelationIdAliases(): void
    {
        $this->insertCategory(42, 'Hardware');
        $this->dataSource->getClient()->exec(
            'CREATE UNIQUE INDEX catalog_listings_category_id_unique ON catalog_listings (category_id)'
        );

        $result = $this->createManager(CatalogListingEntity::class)->upsert(
            CatalogListingEntity::class,
            ['name' => 'Category Conflict Router', 'categoryId' => 42],
            ['categoryId'],
        );

        self::assertTrue($result->isOk());
        self::assertStringContainsString('"category_id"', $result->getRaw());
        self::assertStringNotContainsString('"categoryId"', $result->getRaw());
        self::assertSame(42, $this->fetchCatalogListing('Category Conflict Router')['category_id']);
    }

    public function testInsertDedupeAllowsScalarAndRelationToShareAJoinColumn(): void
    {
        $this->insertCategory(42, 'Hardware');

        $listing = new CatalogListingWithScalarEntity();
        $listing->name = 'Cordless Driver';
        $listing->category = new CatalogCategoryEntity();
        $listing->category->id = 42;

        $result = $this->createManager(CatalogListingWithScalarEntity::class)->insert(
            CatalogListingWithScalarEntity::class,
            $listing,
            new InsertOptions(relations: ['category']),
        );

        self::assertTrue($result->isOk());
        self::assertSame(1, substr_count($result->getRaw(), '"category_id"'));
        self::assertSame(42, $this->fetchCatalogListing('Cordless Driver')['category_id']);
    }

    public function testInsertDedupePreservesZeroRelationAliasValue(): void
    {
        $result = $this->createManager(CatalogListingWithScalarEntity::class)->insert(
            CatalogListingWithScalarEntity::class,
            ['name' => 'Zero Alias Insert', 'category_id' => 0],
        );

        $value = $this->fetchCatalogListing('Zero Alias Insert')['category_id'];
        self::assertTrue($result->isOk());
        self::assertNotNull($value);
        self::assertSame(0, (int)$value);
    }

    public function testUpsertDedupePreservesZeroRelationAliasValue(): void
    {
        $result = $this->createManager(CatalogListingWithScalarEntity::class)->upsert(
            CatalogListingWithScalarEntity::class,
            ['name' => 'Zero Alias Upsert', 'category_id' => 0],
            ['name'],
        );

        $value = $this->fetchCatalogListing('Zero Alias Upsert')['category_id'];
        self::assertTrue($result->isOk());
        self::assertNotNull($value);
        self::assertSame(0, (int)$value);
    }

    public function testUpdateDedupePreservesZeroRelationAliasValue(): void
    {
        $this->insertCatalogListing('Zero Alias Update', 42);

        $result = $this->createManager(CatalogListingWithScalarEntity::class)->update(
            CatalogListingWithScalarEntity::class,
            ['categoryId' => null, 'category_id' => 0],
            ['name' => 'Zero Alias Update'],
        );

        $value = $this->fetchCatalogListing('Zero Alias Update')['category_id'];
        self::assertTrue($result->isOk());
        self::assertNotNull($value);
        self::assertSame(0, (int)$value);
    }

    public function testConflictingScalarAndRelationJoinColumnWritesFailClearly(): void
    {
        $listing = new CatalogListingWithScalarEntity();
        $listing->name = 'Conflicted Listing';
        $listing->categoryId = 7;
        $listing->category = new CatalogCategoryEntity();
        $listing->category->id = 42;

        $this->expectException(ORMException::class);
        $this->expectExceptionMessage('category_id is mapped more than once');

        $this->createManager(CatalogListingWithScalarEntity::class)->insert(
            CatalogListingWithScalarEntity::class,
            $listing,
            new InsertOptions(relations: ['category']),
        );
    }

    public function testConflictingScalarAndRawRelationAliasInsertWritesFailClearly(): void
    {
        $payload = [
            'name' => 'Conflicted Alias Insert',
            'categoryId' => 7,
            'category_id' => 42,
        ];

        $this->expectException(ORMException::class);
        $this->expectExceptionMessage('category_id is mapped more than once');

        $this->createManager(CatalogListingWithScalarEntity::class)->insert(
            CatalogListingWithScalarEntity::class,
            $payload,
        );
    }

    public function testConflictingScalarAndRawRelationAliasUpsertWritesFailClearly(): void
    {
        $payload = [
            'name' => 'Conflicted Alias Upsert',
            'categoryId' => 7,
            'category_id' => 42,
        ];

        $this->expectException(ORMException::class);
        $this->expectExceptionMessage('category_id is mapped more than once');

        $this->createManager(CatalogListingWithScalarEntity::class)->upsert(
            CatalogListingWithScalarEntity::class,
            $payload,
            ['name'],
        );
    }

    /**
     * @param class-string $entityClass
     */
    private function createManager(string $entityClass): EntityManager
    {
        return new EntityManager(
            connection: $this->dataSource,
            query: new SQLQuery(
                db: $this->dataSource->getClient(),
                fetchClass: $entityClass,
                fetchMode: PDO::FETCH_CLASS,
                dialect: SQLDialect::SQLITE,
            ),
        );
    }

    private function insertCatalogItem(string $name, ?string $description): void
    {
        $statement = $this->dataSource->getClient()->prepare(
            'INSERT INTO nullable_catalog_items (name, description) VALUES (?, ?)'
        );
        $statement->execute([$name, $description]);
    }

    private function insertCategory(int $id, string $name): void
    {
        $statement = $this->dataSource->getClient()->prepare(
            'INSERT INTO catalog_categories (id, name) VALUES (?, ?)'
        );
        $statement->execute([$id, $name]);
    }

    private function insertCatalogListing(string $name, ?int $categoryId): void
    {
        $statement = $this->dataSource->getClient()->prepare(
            'INSERT INTO catalog_listings (name, category_id) VALUES (?, ?)'
        );
        $statement->execute([$name, $categoryId]);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchCatalogItem(string $name): array
    {
        $statement = $this->dataSource->getClient()->prepare(
            'SELECT name, description FROM nullable_catalog_items WHERE name = ?'
        );
        $statement->execute([$name]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchCatalogListing(string $name): array
    {
        $statement = $this->dataSource->getClient()->prepare(
            'SELECT id, name, category_id FROM catalog_listings WHERE name = ?'
        );
        $statement->execute([$name]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    private function catalogItemExistsByName(string $name): bool
    {
        $statement = $this->dataSource->getClient()->prepare(
            'SELECT COUNT(*) FROM nullable_catalog_items WHERE name = ?'
        );
        $statement->execute([$name]);

        return (int)$statement->fetchColumn() > 0;
    }

    private function catalogListingExistsById(int $id): bool
    {
        $statement = $this->dataSource->getClient()->prepare(
            'SELECT COUNT(*) FROM catalog_listings WHERE id = ?'
        );
        $statement->execute([$id]);

        return (int)$statement->fetchColumn() > 0;
    }

    private function cleanupSqliteFiles(string $path): void
    {
        foreach ([$path, $path . '-wal', $path . '-shm'] as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}

#[Entity(table: 'nullable_catalog_items')]
class NullableCatalogItemEntity
{
    #[PrimaryGeneratedColumn]
    public ?int $id = null;

    #[Column(type: ColumnType::VARCHAR, nullable: false)]
    public string $name = '';

    #[Column(type: ColumnType::TEXT, nullable: true)]
    public ?string $description = null;
}

class NullableCatalogItemPatch
{
    public ?string $description = null;
}

#[Entity(table: 'catalog_categories')]
class CatalogCategoryEntity
{
    #[PrimaryGeneratedColumn]
    public ?int $id = null;

    #[Column(type: ColumnType::VARCHAR, nullable: false)]
    public string $name = '';
}

class CatalogListingCreatePayload
{
    public string $name = '';
    public ?int $categoryId = null;
}

class CatalogListingRelationPatch
{
    public ?int $categoryId = null;
}

class CatalogListingReadonlyIdPatch
{
    public int $id = 999;
}

#[Entity(table: 'catalog_listings')]
class CatalogListingEntity
{
    #[PrimaryGeneratedColumn]
    public ?int $id = null;

    #[Column(type: ColumnType::VARCHAR, nullable: false)]
    public string $name = '';

    #[ManyToOne(type: CatalogCategoryEntity::class)]
    #[JoinColumn(name: 'category_id', referencedColumnName: 'id')]
    public ?CatalogCategoryEntity $category = null;
}

#[Entity(table: 'catalog_listings')]
class CatalogListingWithScalarEntity
{
    #[PrimaryGeneratedColumn]
    public ?int $id = null;

    #[Column(type: ColumnType::VARCHAR, nullable: false)]
    public string $name = '';

    #[Column(name: 'category_id', type: ColumnType::INT, nullable: true)]
    public ?int $categoryId = null;

    #[ManyToOne(type: CatalogCategoryEntity::class)]
    #[JoinColumn(name: 'category_id', referencedColumnName: 'id')]
    public ?CatalogCategoryEntity $category = null;
}
