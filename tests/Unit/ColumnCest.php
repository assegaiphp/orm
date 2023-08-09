<?php

namespace Tests\Unit;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\CreateDateColumn;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Columns\UpdateDateColumn;
use Assegai\Orm\Exceptions\NotImplementedException;
use Assegai\Orm\Queries\Sql\ColumnType;
use DateTime;
use ReflectionException;
use ReflectionProperty;
use Tests\Support\UnitTester;
use Unit\mocks\MockColorType;

spl_autoload_register(function ($classname) {
  if (str_contains($classname, 'MockColorType'))
  {
    $classname = str_replace('Unit\\mocks\\', '', $classname);

    require __DIR__ . "/mocks/$classname.php";
  }
});

class ColumnCest
{
  #[PrimaryGeneratedColumn]
  private int $idColumn = 0;
  #[Column(name: 'numeric_column', type: ColumnType::INT, unsigned: true)]
  private int $numericColumn = 10;
  #[Column(name: 'string_column', type: ColumnType::VARCHAR)]
  private string $stringColumn = 'Shaka';
  #[CreateDateColumn]
  private ?DateTime $createdAtColumn = null;
  #[UpdateDateColumn]
  private ?DateTime $updatedAtColumn = null;
  #[Column(name: 'spatial_column', type: ColumnType::GEOMETRY)]
  private float $spatialColumn = 0.0;
  #[Column(name: 'enum_column', type: ColumnType::ENUM, default: MockColorType::RED, enum: MockColorType::class)]
  private MockColorType $enumColumn = MockColorType::RED;

  private ?Column $idColumnAttribute = null;
  private ?Column $numericColumnAttribute = null;
  private ?Column $stringColumnAttribute = null;
  private ?Column $createdAtColumnAttribute = null;
  private ?Column $updatedAtColumnAttribute = null;
  private ?Column $spatialColumnAttribute = null;
  private ?Column $enumColumnAttribute = null;

  /**
   * @param UnitTester $I
   * @return void
   * @throws ReflectionException
   */
  public function _before(UnitTester $I): void
  {
    $idColumnReflection = new ReflectionProperty($this, 'idColumn');
    $this->idColumnAttribute = $idColumnReflection->getAttributes(PrimaryGeneratedColumn::class)[0]->newInstance();

    $numericColumnReflection = new ReflectionProperty($this, 'numericColumn');
    $this->numericColumnAttribute = $numericColumnReflection->getAttributes(Column::class)[0]->newInstance();

    $stringColumnReflection = new ReflectionProperty($this, 'stringColumn');
    $this->stringColumnAttribute = $stringColumnReflection->getAttributes(Column::class)[0]->newInstance();

    $createdAtColumnReflection = new ReflectionProperty($this, 'createdAtColumn');
    $this->createdAtColumnAttribute = $createdAtColumnReflection->getAttributes(CreateDateColumn::class)[0]->newInstance();

    $updatedAtColumnReflection = new ReflectionProperty($this, 'updatedAtColumn');
    $this->updatedAtColumnAttribute = $updatedAtColumnReflection->getAttributes(UpdateDateColumn::class)[0]->newInstance();

    $spatialColumnReflection = new ReflectionProperty($this, 'spatialColumn');
    $this->spatialColumnAttribute = $spatialColumnReflection->getAttributes(Column::class)[0]->newInstance();

    $enumColumnReflection = new ReflectionProperty($this, 'enumColumn');
    $this->enumColumnAttribute = $enumColumnReflection->getAttributes(Column::class)[0]->newInstance();
  }

  // tests
  /** @noinspection SpellCheckingInspection */
  /**
   * @param UnitTester $I
   * @return void
   */
  public function testTheGetfieldtypeMethod(UnitTester $I): void
  {
    $idFieldType = $this->idColumnAttribute->getFieldType();
    $numericFieldType = $this->numericColumnAttribute->getFieldType();
    $stringFieldType = $this->stringColumnAttribute->getFieldType();
    $createdAtFieldType = $this->createdAtColumnAttribute->getFieldType();
    $updatedAtFieldType = $this->updatedAtColumnAttribute->getFieldType();
    $spatialFieldType = $this->spatialColumnAttribute->getFieldType();
    $enumFieldType = $this->enumColumnAttribute->getFieldType();

    $I->assertEquals('bigint unsigned', $idFieldType);
    $I->assertEquals('int', $numericFieldType);
    $I->assertEquals('varchar(255)', $stringFieldType);
    $I->assertEquals('datetime', $createdAtFieldType);
    $I->assertEquals('datetime', $updatedAtFieldType);
    $I->assertEquals('geometry', $spatialFieldType);
    $I->assertEquals("enum('RED','ORANGE','YELLOW','GREEN','BLUE','INDIGO','VIOLET')", $enumFieldType);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheGetfieldextraMethod(UnitTester $I): void
  {
    $idFieldTExtra = $this->idColumnAttribute->getFieldExtra();
    $numericFieldTExtra = $this->numericColumnAttribute->getFieldExtra();
    $stringFieldTExtra = $this->stringColumnAttribute->getFieldExtra();
    $createdAtFieldTExtra = $this->createdAtColumnAttribute->getFieldExtra();
    $updatedAtFieldTExtra = $this->updatedAtColumnAttribute->getFieldExtra();
    $spatialFieldTExtra = $this->spatialColumnAttribute->getFieldExtra();
    $enumFieldExtra = $this->enumColumnAttribute->getFieldExtra();

    $I->assertEquals('auto_increment', $idFieldTExtra);
    $I->assertEquals('', $numericFieldTExtra);
    $I->assertEquals('', $stringFieldTExtra);
    $I->assertEquals('DEFAULT_GENERATED', $createdAtFieldTExtra);
    $I->assertEquals('DEFAULT_GENERATED on update CURRENT_TIMESTAMP', $updatedAtFieldTExtra);
    $I->assertEquals('', $spatialFieldTExtra);
    $I->assertEquals('', $enumFieldExtra);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheGetlengthMethod(UnitTester $I): void
  {
    $idFieldLength = $this->idColumnAttribute->getLength();
    $numericFieldLength = $this->numericColumnAttribute->getLength();
    $stringFieldLength = $this->stringColumnAttribute->getLength();
    $createdAtFieldLength = $this->createdAtColumnAttribute->getLength();
    $updatedAtFieldLength = $this->updatedAtColumnAttribute->getLength();
    $spatialFieldLength = $this->spatialColumnAttribute->getLength();
    $enumFieldLength = $this->enumColumnAttribute->getLength();

    $I->assertNull($idFieldLength);
    $I->assertNull($numericFieldLength);
    $I->assertEquals(Column::DEFAULT_LENGTH_VARCHAR, $stringFieldLength);
    $I->assertNull($createdAtFieldLength);
    $I->assertNull($updatedAtFieldLength);
    $I->assertNull($spatialFieldLength);
    $I->assertNull($enumFieldLength);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheGetValuesMethod(UnitTester $I): void
  {
    $idFieldValues = $this->idColumnAttribute->getValues();
    $numericFieldValues = $this->numericColumnAttribute->getValues();
    $stringFieldValues = $this->stringColumnAttribute->getValues();
    $createdAtFieldValues = $this->createdAtColumnAttribute->getValues();
    $updatedAtFieldValues = $this->updatedAtColumnAttribute->getValues();
    $spatialFieldValues = $this->spatialColumnAttribute->getValues();
    $enumFieldValues = $this->enumColumnAttribute->getValues();

    $I->assertNull($idFieldValues);
    $I->assertNull($numericFieldValues);
    $I->assertNull($stringFieldValues);
    $I->assertNull($createdAtFieldValues);
    $I->assertNull($updatedAtFieldValues);
    $I->assertNull($spatialFieldValues);
    $I->assertEquals(MockColorType::cases(), $enumFieldValues);
  }

  /** @noinspection SpellCheckingInspection */
  public function testTheGetValuesasstringMethod(UnitTester $I): void
  {
    $idFieldValues = $this->idColumnAttribute->getValuesAsString();
    $numericFieldValues = $this->numericColumnAttribute->getValuesAsString();
    $stringFieldValues = $this->stringColumnAttribute->getValuesAsString();
    $createdAtFieldValues = $this->createdAtColumnAttribute->getValuesAsString();
    $updatedAtFieldValues = $this->updatedAtColumnAttribute->getValuesAsString();
    $spatialFieldValues = $this->spatialColumnAttribute->getValuesAsString();
    $enumFieldValues = $this->enumColumnAttribute->getValuesAsString();
    $enumFieldValuesAsString = implode(',', array_map(fn($item) => "'$item->value'", MockColorType::cases()));

    $I->assertEmpty($idFieldValues);
    $I->assertEmpty($numericFieldValues);
    $I->assertEmpty($stringFieldValues);
    $I->assertEmpty($createdAtFieldValues);
    $I->assertEmpty($updatedAtFieldValues);
    $I->assertEmpty($spatialFieldValues);
    $I->assertEquals($enumFieldValuesAsString, $enumFieldValues);
  }
}
