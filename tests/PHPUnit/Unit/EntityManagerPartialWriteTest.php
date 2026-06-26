<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\DeleteDateColumn;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Attributes\Relations\JoinColumn;
use Assegai\Orm\Attributes\Relations\ManyToOne;
use Assegai\Orm\Attributes\TypeConverter;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Exceptions\TypeConversionException;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Management\Options\InsertOptions;
use Assegai\Orm\Management\Options\UpdateOptions;
use Assegai\Orm\Management\Repository;
use Assegai\Orm\Queries\Sql\ColumnType;
use Assegai\Orm\Queries\Sql\SQLQuery;
use DateTime;
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
                NullableCatalogItemScheduleEntity::class,
                CatalogCategoryEntity::class,
                CatalogListingEntity::class,
                CatalogListingWithScalarEntity::class,
                CatalogListingWithAliasCollisionEntity::class,
                SoftRemovableCatalogItemEntity::class,
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
        $this->dataSource->getClient()->exec(
            'CREATE TABLE catalog_listing_alias_collisions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                category_code INTEGER NULL,
                category_id INTEGER NULL
            )'
        );
        $this->dataSource->getClient()->exec(
            'CREATE TABLE soft_removable_catalog_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                status TEXT NOT NULL,
                deleted_at TEXT NULL
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

    public function testRelationIdAliasUpdateNormalizesRelatedObjectValue(): void
    {
        $this->insertCategory(42, 'Hardware');
        $this->insertCatalogListing('Object Alias Update', null);

        $category = new CatalogCategoryEntity();
        $category->id = 42;

        $result = $this->createManager(CatalogListingEntity::class)->update(
            CatalogListingEntity::class,
            ['categoryId' => $category],
            ['name' => 'Object Alias Update'],
        );

        self::assertTrue($result->isOk());
        self::assertSame(42, $this->fetchCatalogListing('Object Alias Update')['category_id']);
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

    public function testObjectPartialUpdateSkipsNullsForNonNullableDefaults(): void
    {
        $statement = $this->dataSource->getClient()->prepare(
            'INSERT INTO soft_removable_catalog_items (name, status, deleted_at) VALUES (?, ?, NULL)'
        );
        $statement->execute(['Pending Update', CatalogItemStatus::ACTIVE->value]);
        $id = (int)$this->dataSource->getClient()->lastInsertId();

        $payload = new SoftRemovableCatalogItemWritePayload();
        $payload->name = 'Updated Without Status';

        $result = $this->createManager(SoftRemovableCatalogItemEntity::class)->update(
            SoftRemovableCatalogItemEntity::class,
            $payload,
            ['id' => $id],
        );
        $row = $this->fetchSoftRemovableCatalogItem($id);

        self::assertTrue($result->isOk());
        self::assertSame('Updated Without Status', $row['name']);
        self::assertSame(CatalogItemStatus::ACTIVE->value, $row['status']);
    }

    public function testManagerUpsertUsesDefaultsForNonNullableNullsOnObjectPayloads(): void
    {
        $payload = new SoftRemovableCatalogItemWritePayload();
        $payload->name = 'Manager Upsert Default Status';

        $result = $this->createManager(SoftRemovableCatalogItemEntity::class)->upsert(
            SoftRemovableCatalogItemEntity::class,
            $payload,
            ['name'],
        );
        $row = $this->fetchSoftRemovableCatalogItemByName('Manager Upsert Default Status');

        self::assertTrue($result->isOk());
        self::assertSame(CatalogItemStatus::ACTIVE->value, $row['status']);
    }

    public function testManagerUpdateConvertsBackedEnumScalarsOnObjectPayloads(): void
    {
        $statement = $this->dataSource->getClient()->prepare(
            'INSERT INTO soft_removable_catalog_items (name, status, deleted_at) VALUES (?, ?, NULL)'
        );
        $statement->execute(['Pending Scalar Update', CatalogItemStatus::ACTIVE->value]);
        $id = (int)$this->dataSource->getClient()->lastInsertId();

        $payload = new SoftRemovableCatalogItemRawWritePayload();
        $payload->name = 'Updated With Scalar Status';
        $payload->status = CatalogItemStatus::DRAFT->value;

        $result = $this->createManager(SoftRemovableCatalogItemEntity::class)->update(
            SoftRemovableCatalogItemEntity::class,
            $payload,
            ['id' => $id],
        );
        $row = $this->fetchSoftRemovableCatalogItem($id);

        self::assertTrue($result->isOk());
        self::assertSame('Updated With Scalar Status', $row['name']);
        self::assertSame(CatalogItemStatus::DRAFT->value, $row['status']);
    }

    public function testManagerUpsertConvertsBackedEnumScalarsOnObjectPayloads(): void
    {
        $payload = new SoftRemovableCatalogItemRawWritePayload();
        $payload->name = 'Manager Upsert Scalar Status';
        $payload->status = CatalogItemStatus::DRAFT->value;

        $result = $this->createManager(SoftRemovableCatalogItemEntity::class)->upsert(
            SoftRemovableCatalogItemEntity::class,
            $payload,
            ['name'],
        );
        $row = $this->fetchSoftRemovableCatalogItemByName('Manager Upsert Scalar Status');

        self::assertTrue($result->isOk());
        self::assertSame(CatalogItemStatus::DRAFT->value, $row['status']);
    }

    public function testRepositoryUpsertSkipsNullsForNonNullableDefaultsOnPartialObjects(): void
    {
        $payload = new SoftRemovableCatalogItemWritePayload();
        $payload->name = 'Repository Upsert Default Status';

        $repository = new Repository(
            SoftRemovableCatalogItemEntity::class,
            $this->createManager(SoftRemovableCatalogItemEntity::class),
        );
        $result = $repository->upsert($payload, ['name']);
        $row = $this->fetchSoftRemovableCatalogItemByName('Repository Upsert Default Status');

        self::assertTrue($result->isOk());
        self::assertSame(CatalogItemStatus::ACTIVE->value, $row['status']);
    }

    public function testRepositoryUpsertConvertsBackedEnumScalarsOnPartialObjects(): void
    {
        $payload = new SoftRemovableCatalogItemRawWritePayload();
        $payload->name = 'Repository Upsert Scalar Status';
        $payload->status = CatalogItemStatus::DRAFT->value;

        $repository = new Repository(
            SoftRemovableCatalogItemEntity::class,
            $this->createManager(SoftRemovableCatalogItemEntity::class),
        );
        $result = $repository->upsert($payload, ['name']);
        $row = $this->fetchSoftRemovableCatalogItemByName('Repository Upsert Scalar Status');

        self::assertTrue($result->isOk());
        self::assertSame(CatalogItemStatus::DRAFT->value, $row['status']);
    }

    public function testCustomConverterNullResultIsPreservedForNullableTargets(): void
    {
        $payload = new CatalogItemSchedulePayload();
        $payload->availableAt = '';

        $manager = $this->createManager(NullableCatalogItemScheduleEntity::class);
        $manager->useConverters([new BlankStringToDateTimeConverter()]);

        $entity = $manager->getEntityFromObject(NullableCatalogItemScheduleEntity::class, $payload);

        self::assertNull($entity->availableAt);
    }

    public function testRepositoryUpsertRejectsNullForRequiredColumnsWithoutDefaults(): void
    {
        $payload = new NullableCatalogItemNullNamePayload();

        $repository = new Repository(
            NullableCatalogItemEntity::class,
            $this->createManager(NullableCatalogItemEntity::class),
        );

        $this->expectException(TypeConversionException::class);
        $this->expectExceptionMessage('Cannot assign null to non-nullable property name.');

        $repository->upsert($payload, ['name']);
    }

    public function testRepositorySoftRemoveConvertsBackedEnumScalarsOnPartialObjects(): void
    {
        $statement = $this->dataSource->getClient()->prepare(
            'INSERT INTO soft_removable_catalog_items (name, status, deleted_at) VALUES (?, ?, NULL)'
        );
        $statement->execute(['Archive Scalar Candidate', CatalogItemStatus::ACTIVE->value]);
        $id = (int)$this->dataSource->getClient()->lastInsertId();

        $payload = new SoftRemovableCatalogItemRawDeletionPayload();
        $payload->id = $id;
        $payload->status = CatalogItemStatus::ACTIVE->value;

        $repository = new Repository(
            SoftRemovableCatalogItemEntity::class,
            $this->createManager(SoftRemovableCatalogItemEntity::class),
        );
        $result = $repository->softRemove($payload);
        $row = $this->fetchSoftRemovableCatalogItem($id);

        self::assertTrue($result->isOk());
        self::assertSame(CatalogItemStatus::ACTIVE->value, $row['status']);
        self::assertNotNull($row['deleted_at']);
    }

    public function testRepositoryUpsertRejectsInvalidBackedEnumScalarsClearly(): void
    {
        $payload = new SoftRemovableCatalogItemRawWritePayload();
        $payload->name = 'Repository Upsert Invalid Status';
        $payload->status = 'archived';

        $repository = new Repository(
            SoftRemovableCatalogItemEntity::class,
            $this->createManager(SoftRemovableCatalogItemEntity::class),
        );

        $this->expectException(TypeConversionException::class);
        $this->expectExceptionMessage('Cannot convert value for status');

        $repository->upsert($payload, ['name']);
    }
    public function testRepositorySoftRemoveSkipsNullsForNonNullableDefaultsOnPartialObjects(): void
    {
        $statement = $this->dataSource->getClient()->prepare(
            'INSERT INTO soft_removable_catalog_items (name, status, deleted_at) VALUES (?, ?, NULL)'
        );
        $statement->execute(['Archive Candidate', CatalogItemStatus::ACTIVE->value]);
        $id = (int)$this->dataSource->getClient()->lastInsertId();

        $payload = new SoftRemovableCatalogItemDeletionPayload();
        $payload->id = $id;

        $repository = new Repository(
            SoftRemovableCatalogItemEntity::class,
            $this->createManager(SoftRemovableCatalogItemEntity::class),
        );
        $result = $repository->softRemove($payload);
        $row = $this->fetchSoftRemovableCatalogItem($id);

        self::assertTrue($result->isOk());
        self::assertSame(CatalogItemStatus::ACTIVE->value, $row['status']);
        self::assertNotNull($row['deleted_at']);
    }

    public function testInsertAcceptsListArrayRows(): void
    {
        $this->insertCategory(42, 'Hardware');

        $result = $this->createManager(CatalogListingEntity::class)->insert(
            CatalogListingEntity::class,
            [
                ['name' => 'Bulk Cordless Saw', 'categoryId' => 42],
                ['name' => 'Bulk Bench Plane', 'category_id' => 42],
            ],
        );

        $firstRow = $this->fetchCatalogListing('Bulk Cordless Saw');
        $secondRow = $this->fetchCatalogListing('Bulk Bench Plane');
        $identifiers = $result->getIdentifiers()->results ?? [];
        $generatedMaps = $result->getGeneratedMaps()->results ?? [];

        self::assertTrue($result->isOk());
        self::assertSame(2, $result->getTotalAffectedRows());
        self::assertStringContainsString('), (', $result->getRaw());
        self::assertSame(42, $firstRow['category_id']);
        self::assertSame(42, $secondRow['category_id']);
        self::assertCount(2, $identifiers);
        self::assertCount(2, $generatedMaps);
        self::assertSame((int)$firstRow['id'], $identifiers[0]->id);
        self::assertSame((int)$secondRow['id'], $identifiers[1]->id);
        self::assertSame((int)$firstRow['id'], $generatedMaps[0]->id);
        self::assertSame((int)$secondRow['id'], $generatedMaps[1]->id);
    }

    public function testCreatePreservesNonNullValuesWhenNullableConversionIsMissing(): void
    {
        $entity = $this->createManager(NullableCatalogItemEntity::class)->create(
            NullableCatalogItemEntity::class,
            ['name' => 'Created Nullable Conversion', 'description' => 123],
        );

        self::assertSame('123', $entity->description);
    }

    public function testInsertPreservesNonNullValuesWhenNullableConversionIsMissing(): void
    {
        $result = $this->createManager(NullableCatalogItemEntity::class)->insert(
            NullableCatalogItemEntity::class,
            ['name' => 'Inserted Nullable Conversion', 'description' => 123],
        );
        $row = $this->fetchCatalogItem('Inserted Nullable Conversion');

        self::assertTrue($result->isOk());
        self::assertSame('123', $row['description']);
    }

    public function testUpsertPreservesNonNullValuesWhenNullableConversionIsMissing(): void
    {
        $result = $this->createManager(NullableCatalogItemEntity::class)->upsert(
            NullableCatalogItemEntity::class,
            ['name' => 'Upserted Nullable Conversion', 'description' => 123],
            ['name'],
        );
        $row = $this->fetchCatalogItem('Upserted Nullable Conversion');

        self::assertTrue($result->isOk());
        self::assertSame('123', $row['description']);
    }

    public function testSqliteBulkInsertPopulatesGeneratedIdsWhenRowsMixExplicitAndGeneratedIds(): void
    {
        $result = $this->createManager(NullableCatalogItemEntity::class)->insert(
            NullableCatalogItemEntity::class,
            [
                ['id' => 100, 'name' => 'Bulk Explicit Id', 'description' => 'supplied id'],
                ['name' => 'Bulk Generated Id', 'description' => 'generated id'],
            ],
            new InsertOptions(readonlyColumns: ['createdAt', 'updatedAt', 'deletedAt']),
        );

        $explicitRow = $this->fetchCatalogItem('Bulk Explicit Id');
        $generatedRow = $this->fetchCatalogItem('Bulk Generated Id');
        $identifiers = $result->getIdentifiers()->results ?? [];
        $generatedMaps = $result->getGeneratedMaps()->results ?? [];

        self::assertTrue($result->isOk());
        self::assertSame(2, $result->getTotalAffectedRows());
        self::assertCount(2, $identifiers);
        self::assertCount(2, $generatedMaps);
        self::assertSame(100, $identifiers[0]->id);
        self::assertSame(100, $generatedMaps[0]->id);
        self::assertSame((int)$generatedRow['id'], $identifiers[1]->id);
        self::assertSame((int)$generatedRow['id'], $generatedMaps[1]->id);
        self::assertGreaterThan(0, $identifiers[1]->id);
        self::assertNotSame($identifiers[0]->id, $identifiers[1]->id);
        self::assertSame(100, (int)$explicitRow['id']);
    }

    public function testBulkInsertRejectsRowsWithNumericKeysBeforeWriting(): void
    {
        $result = $this->createManager(NullableCatalogItemEntity::class)->insert(
            NullableCatalogItemEntity::class,
            [
                ['name' => 'Bulk Widget'],
                ['Numeric Key Item'],
            ],
        );

        self::assertTrue($result->isError());
        self::assertInstanceOf(ORMException::class, $result->getErrors()[0] ?? null);
        self::assertFalse($this->catalogItemExistsByName('Bulk Widget'));
        self::assertFalse($this->catalogItemExistsByName('Numeric Key Item'));
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

    public function testUpsertRejectsInvalidAssociativePayloadBeforeHydration(): void
    {
        $result = $this->createManager(NullableCatalogItemEntity::class)->upsert(
            NullableCatalogItemEntity::class,
            ['name' => 'Invalid Upsert Payload', 'descrption' => 'typo should be rejected'],
            ['name'],
        );

        self::assertTrue($result->isError());
        self::assertInstanceOf(ORMException::class, $result->getErrors()[0] ?? null);
        self::assertFalse($this->catalogItemExistsByName('Invalid Upsert Payload'));
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

    public function testDeclaredColumnWinsOverGeneratedRelationAliasOnInsert(): void
    {
        $result = $this->createManager(CatalogListingWithAliasCollisionEntity::class)->insert(
            CatalogListingWithAliasCollisionEntity::class,
            ['name' => 'Alias Collision Insert', 'categoryId' => 7],
        );

        $row = $this->fetchAliasCollisionListing('Alias Collision Insert');
        self::assertTrue($result->isOk());
        self::assertSame(7, (int)$row['category_code']);
        self::assertNull($row['category_id']);
    }

    public function testDeclaredColumnWinsOverGeneratedRelationAliasOnUpsert(): void
    {
        $result = $this->createManager(CatalogListingWithAliasCollisionEntity::class)->upsert(
            CatalogListingWithAliasCollisionEntity::class,
            ['name' => 'Alias Collision Upsert', 'categoryId' => 7],
            ['name'],
        );

        $row = $this->fetchAliasCollisionListing('Alias Collision Upsert');
        self::assertTrue($result->isOk());
        self::assertSame(7, (int)$row['category_code']);
        self::assertNull($row['category_id']);
    }

    public function testUpsertConflictPathPrefersDeclaredColumnOverGeneratedRelationAlias(): void
    {
        $this->dataSource->getClient()->exec(
            'CREATE UNIQUE INDEX catalog_listing_alias_collision_category_code_unique ON catalog_listing_alias_collisions (category_code)'
        );

        $result = $this->createManager(CatalogListingWithAliasCollisionEntity::class)->upsert(
            CatalogListingWithAliasCollisionEntity::class,
            ['name' => 'Alias Collision Conflict Path', 'categoryId' => 7],
            ['categoryId'],
        );

        $row = $this->fetchAliasCollisionListing('Alias Collision Conflict Path');
        self::assertTrue($result->isOk());
        self::assertStringContainsString('ON CONFLICT ("category_code")', $result->getRaw());
        self::assertStringNotContainsString('ON CONFLICT ("category_id")', $result->getRaw());
        self::assertSame(7, (int)$row['category_code']);
        self::assertNull($row['category_id']);
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
            'SELECT id, name, description FROM nullable_catalog_items WHERE name = ?'
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

    /**
     * @return array<string, mixed>
     */
    private function fetchAliasCollisionListing(string $name): array
    {
        $statement = $this->dataSource->getClient()->prepare(
            'SELECT id, name, category_code, category_id FROM catalog_listing_alias_collisions WHERE name = ?'
        );
        $statement->execute([$name]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchSoftRemovableCatalogItem(int $id): array
    {
        $statement = $this->dataSource->getClient()->prepare(
            'SELECT id, name, status, deleted_at FROM soft_removable_catalog_items WHERE id = ?'
        );
        $statement->execute([$id]);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }
    /**
     * @return array<string, mixed>
     */
    private function fetchSoftRemovableCatalogItemByName(string $name): array
    {
        $statement = $this->dataSource->getClient()->prepare(
            'SELECT id, name, status, deleted_at FROM soft_removable_catalog_items WHERE name = ?'
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

enum CatalogItemStatus: string
{
    case ACTIVE = 'active';
    case DRAFT = 'draft';
}

#[Entity(table: 'soft_removable_catalog_items')]
class SoftRemovableCatalogItemEntity
{
    #[PrimaryGeneratedColumn]
    public ?int $id = null;

    #[Column(type: ColumnType::VARCHAR, nullable: false)]
    public string $name = '';

    #[Column(type: ColumnType::ENUM, nullable: false, default: CatalogItemStatus::ACTIVE, enum: CatalogItemStatus::class)]
    public CatalogItemStatus $status = CatalogItemStatus::ACTIVE;

    #[DeleteDateColumn]
    public ?string $deletedAt = null;
}

class SoftRemovableCatalogItemDeletionPayload
{
    public ?int $id = null;
    public ?CatalogItemStatus $status = null;
}
class SoftRemovableCatalogItemWritePayload
{
    public string $name = '';
    public ?CatalogItemStatus $status = null;
}
class SoftRemovableCatalogItemRawDeletionPayload
{
    public ?int $id = null;
    public string $status = '';
}

class SoftRemovableCatalogItemRawWritePayload
{
    public string $name = '';
    public string $status = '';
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

class NullableCatalogItemNullNamePayload
{
    public ?string $name = null;
}

#[Entity(table: 'nullable_catalog_item_schedules')]
class NullableCatalogItemScheduleEntity
{
    #[PrimaryGeneratedColumn]
    public ?int $id = null;

    #[Column(type: ColumnType::DATETIME, nullable: true)]
    public ?DateTime $availableAt = null;
}

class CatalogItemSchedulePayload
{
    public string $availableAt = '';
}

class BlankStringToDateTimeConverter
{
    #[TypeConverter]
    public function fromStringToDateTime(string $value): ?DateTime
    {
        return trim($value) === '' ? null : new DateTime($value);
    }
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

#[Entity(table: 'catalog_listing_alias_collisions')]
class CatalogListingWithAliasCollisionEntity
{
    #[PrimaryGeneratedColumn]
    public ?int $id = null;

    #[Column(type: ColumnType::VARCHAR, nullable: false)]
    public string $name = '';

    #[Column(name: 'category_code', type: ColumnType::INT, nullable: true)]
    public ?int $categoryId = null;

    #[ManyToOne(type: CatalogCategoryEntity::class)]
    #[JoinColumn(name: 'category_id', referencedColumnName: 'id')]
    public ?CatalogCategoryEntity $category = null;
}
