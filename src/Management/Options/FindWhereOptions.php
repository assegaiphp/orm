<?php

namespace Assegai\Orm\Management\Options;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Util\SqlIdentifier;
use DateTimeInterface;
use ReflectionClass;
use ReflectionException;
use UnitEnum;

/**
 * Class FindWhereOptions. Defines the options for the FindWhere method.
 *
 * @package Assegai\Orm\Management\Options
 */
final readonly class FindWhereOptions
{
    /**
     * @var string|null
     */
    private ?string $tableName;

    /**
     * @param object|array<string, mixed> $conditions The conditions to search for.
     * @param string[] $exclude The columns to exclude.
     * @param class-string|null $entityClass The entity class.
     * @param bool $withRealTotal The flag to include the real count.
     * @throws ORMException
     */
    public function __construct(
        public object|array $conditions,
        public array        $exclude = ['password'],
        private ?string     $entityClass = null,
        public bool         $withRealTotal = false
    )
    {
        $tableName = null;

        if ($this->entityClass) {
            try {
                $reflectionClass = new ReflectionClass($this->entityClass);
                $entityAttributes = $reflectionClass->getAttributes(Entity::class);

                foreach ($entityAttributes as $entityAttribute) {
                    /** @var Entity $entityMetadata */
                    $entityMetadata = (object)$entityAttribute->getArguments();
                    $tableName = $entityMetadata->table;
                }
            } catch (ReflectionException $e) {
                throw new ORMException($e->getMessage());
            }
        }

        $this->tableName = $tableName;
    }

    /**
     * Creates a new FindWhereOptions instance from an array.
     *
     * @param array{conditions: ?array<string, mixed>, exclude: ?string[], entity_class: ?class-string, with_real_total: ?bool} $options The options.
     * @return FindWhereOptions The FindWhereOptions instance.
     * @throws ORMException
     */
    public static function fromArray(array $options): FindWhereOptions
    {
        $conditions = $options['conditions'] ?? $options;
        $exclude = $options['exclude'] ?? ['password'];
        $entityClassName = $options['entity_class'] ?? null;
        $withRealTotal = $options['with_real_total'] ?? false;

        return new FindWhereOptions(
            conditions: $conditions,
            exclude: $exclude,
            entityClass: $entityClassName,
            withRealTotal: $withRealTotal
        );
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    public function __toString(): string
    {
        return $this->buildConditionString(
            renderer: fn(string $identifier, mixed $value): string => match (true) {
                is_null($value), $value === 'NULL' => "$identifier IS NULL",
                is_array($value) && array_is_list($value) && !empty($value) => $identifier . ' IN (' . implode(', ', array_map(
                        fn(mixed $item): string => $this->stringifyValue($item),
                        $value
                    )) . ')',
                default => $identifier . '=' . $this->stringifyValue($value),
            }
        );
    }

    /**
     * @param callable(string, mixed): string $renderer
     * @return string
     */
    private function buildConditionString(callable $renderer): string
    {
        $parts = [];

        foreach ($this->conditions as $key => $value) {
            $parts[] = $renderer(
                $this->qualifyColumnName($this->resolveColumnName((string)$key)),
                $value
            );
        }

        return implode(' AND ', $parts);
    }

    /**
     * @param string $columnName
     * @return string
     */
    private function qualifyColumnName(string $columnName): string
    {
        $qualifiedColumn = $this->tableName
            ? "{$this->tableName}.{$columnName}"
            : $columnName;

        return SqlIdentifier::quote($qualifiedColumn);
    }

    /**
     * @param string $propertyOrColumn
     * @return string
     * @throws ORMException
     */
    private function resolveColumnName(string $propertyOrColumn): string
    {
        if (!$this->entityClass || !property_exists($this->entityClass, $propertyOrColumn)) {
            return $propertyOrColumn;
        }

        try {
            $property = new \ReflectionProperty($this->entityClass, $propertyOrColumn);

            foreach ($property->getAttributes() as $attribute) {
                $attributeInstance = $attribute->newInstance();

                if ($attributeInstance instanceof Column) {
                    return $attribute->getArguments()['name'] ?? $propertyOrColumn;
                }
            }
        } catch (ReflectionException $e) {
            throw new ORMException($e->getMessage());
        }

        return $propertyOrColumn;
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function stringifyValue(mixed $value): string
    {
        if ($value instanceof UnitEnum && property_exists($value, 'value')) {
            $value = $value->value;
        }

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }

        return match (true) {
            is_bool($value) => $value ? '1' : '0',
            is_int($value), is_float($value) => (string)$value,
            is_array($value), is_object($value) => "'" . addslashes(json_encode($value)) . "'",
            default => "'" . addslashes((string)$value) . "'",
        };
    }

    /**
     * Compiles the where conditions and binds values onto the query.
     *
     * @param SQLQuery $query
     * @return string
     */
    public function compile(SQLQuery $query): string
    {
        return $this->buildConditionString(
            renderer: function (string $identifier, mixed $value) use ($query): string {
                if (is_null($value) || $value === 'NULL') {
                    return "$identifier IS NULL";
                }

                if (is_array($value) && array_is_list($value)) {
                    if (empty($value)) {
                        return '1 = 0';
                    }

                    return $identifier . ' IN (' . implode(', ', $query->addParams($value)) . ')';
                }

                return $identifier . '=' . $query->addParam($value);
            }
        );
    }
}
