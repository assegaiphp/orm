<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\CreateDateColumn;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Columns\UpdateDateColumn;
use Assegai\Orm\Queries\Sql\ColumnType;
use DateTime;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Unit\mocks\MockColorType;

final class ColumnTest extends TestCase
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

    public function testGetFieldTypeReturnsExpectedValues(): void
    {
        self::assertSame('bigint unsigned', $this->attribute('idColumn', PrimaryGeneratedColumn::class)->getFieldType());
        self::assertSame('int', $this->attribute('numericColumn')->getFieldType());
        self::assertSame('varchar(255)', $this->attribute('stringColumn')->getFieldType());
        self::assertSame('datetime', $this->attribute('createdAtColumn', CreateDateColumn::class)->getFieldType());
        self::assertSame('datetime', $this->attribute('updatedAtColumn', UpdateDateColumn::class)->getFieldType());
        self::assertSame('geometry', $this->attribute('spatialColumn')->getFieldType());
        self::assertSame(
            "enum('RED','ORANGE','YELLOW','GREEN','BLUE','INDIGO','VIOLET')",
            $this->attribute('enumColumn')->getFieldType()
        );
    }

    public function testGetFieldExtraReturnsExpectedValues(): void
    {
        self::assertSame('auto_increment', $this->attribute('idColumn', PrimaryGeneratedColumn::class)->getFieldExtra());
        self::assertSame('', $this->attribute('numericColumn')->getFieldExtra());
        self::assertSame('', $this->attribute('stringColumn')->getFieldExtra());
        self::assertSame('DEFAULT_GENERATED', $this->attribute('createdAtColumn', CreateDateColumn::class)->getFieldExtra());
        self::assertSame(
            'DEFAULT_GENERATED on update CURRENT_TIMESTAMP',
            $this->attribute('updatedAtColumn', UpdateDateColumn::class)->getFieldExtra()
        );
        self::assertSame('', $this->attribute('spatialColumn')->getFieldExtra());
        self::assertSame('', $this->attribute('enumColumn')->getFieldExtra());
    }

    public function testGetLengthReturnsExpectedValues(): void
    {
        self::assertNull($this->attribute('idColumn', PrimaryGeneratedColumn::class)->getLength());
        self::assertNull($this->attribute('numericColumn')->getLength());
        self::assertSame(Column::DEFAULT_LENGTH_VARCHAR, $this->attribute('stringColumn')->getLength());
        self::assertNull($this->attribute('createdAtColumn', CreateDateColumn::class)->getLength());
        self::assertNull($this->attribute('updatedAtColumn', UpdateDateColumn::class)->getLength());
        self::assertNull($this->attribute('spatialColumn')->getLength());
        self::assertNull($this->attribute('enumColumn')->getLength());
    }

    public function testGetValuesReturnsEnumCasesOnlyForEnumColumns(): void
    {
        self::assertNull($this->attribute('idColumn', PrimaryGeneratedColumn::class)->getValues());
        self::assertNull($this->attribute('numericColumn')->getValues());
        self::assertNull($this->attribute('stringColumn')->getValues());
        self::assertNull($this->attribute('createdAtColumn', CreateDateColumn::class)->getValues());
        self::assertNull($this->attribute('updatedAtColumn', UpdateDateColumn::class)->getValues());
        self::assertNull($this->attribute('spatialColumn')->getValues());
        self::assertSame(MockColorType::cases(), $this->attribute('enumColumn')->getValues());
    }

    public function testGetValuesAsStringReturnsExpectedEnumSqlLiteralList(): void
    {
        self::assertSame('', $this->attribute('idColumn', PrimaryGeneratedColumn::class)->getValuesAsString());
        self::assertSame('', $this->attribute('numericColumn')->getValuesAsString());
        self::assertSame('', $this->attribute('stringColumn')->getValuesAsString());
        self::assertSame('', $this->attribute('createdAtColumn', CreateDateColumn::class)->getValuesAsString());
        self::assertSame('', $this->attribute('updatedAtColumn', UpdateDateColumn::class)->getValuesAsString());
        self::assertSame('', $this->attribute('spatialColumn')->getValuesAsString());

        $expected = implode(',', array_map(static fn(MockColorType $item): string => "'{$item->value}'", MockColorType::cases()));

        self::assertSame($expected, $this->attribute('enumColumn')->getValuesAsString());
    }

    public function testGetSqlDefinitionPreservesEnumValues(): void
    {
        $sqlDefinition = (string) $this->attribute('enumColumn')->getSqlDefinition();

        self::assertStringContainsString(
            "ENUM('RED', 'ORANGE', 'YELLOW', 'GREEN', 'BLUE', 'INDIGO', 'VIOLET')",
            $sqlDefinition
        );
    }

    private function attribute(string $propertyName, string $attributeClass = Column::class): Column
    {
        $property = new ReflectionProperty($this, $propertyName);
        $attributes = $property->getAttributes($attributeClass);

        self::assertNotEmpty($attributes);

        return $attributes[0]->newInstance();
    }
}
