<?php

namespace Assegai\Orm\Management\Options;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Enumerations\SQLDialect;
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
    private const array OPTION_KEYS = [
        'conditions',
        'condition',
        'exclude',
        'exclude_explicit',
        'entity_class',
        'with_real_total',
        'hydrate',
    ];

    /**
     * @var string|null
     */
    private ?string $tableName;
    public array $exclude;
    public bool $excludeIsExplicit;

    /**
     * @param object|array<string, mixed> $conditions The conditions to search for.
     * @param string[]|null $exclude The columns to exclude, or null to use the secure defaults.
     * @param bool $hydrate Whether to hydrate rows into entity instances.
     * @param class-string|null $entityClass The entity class.
     * @param bool $withRealTotal The flag to include the real count.
     * @throws ORMException
     */
    public function __construct(
        public object|array $conditions,
        ?array              $exclude = null,
        private ?string     $entityClass = null,
        public bool         $withRealTotal = false,
        public bool         $hydrate = false,
    )
    {
        $this->excludeIsExplicit = $exclude !== null;
        $this->exclude = $exclude ?? FindOptions::DEFAULT_EXCLUDE;
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
     * @param array<string, mixed> $options Conditions in shorthand form or an options array containing `conditions`.
     * @return FindWhereOptions The FindWhereOptions instance.
     * @throws ORMException
     */
    public static function fromArray(array $options): FindWhereOptions
    {
        $conditions = match (true) {
            array_key_exists('conditions', $options) => $options['conditions'] ?? [],
            array_key_exists('condition', $options) => $options['condition'] ?? [],
            default => array_diff_key($options, array_flip(self::OPTION_KEYS)),
        };
        $excludeIsExplicit = $options['exclude_explicit'] ?? array_key_exists('exclude', $options);
        $exclude = $excludeIsExplicit ? ($options['exclude'] ?? []) : null;
        $entityClassName = $options['entity_class'] ?? null;
        $withRealTotal = $options['with_real_total'] ?? false;
        $hydrate = $options['hydrate'] ?? false;

        return new FindWhereOptions(
            conditions: $conditions,
            exclude: $exclude,
            entityClass: $entityClassName,
            withRealTotal: $withRealTotal,
            hydrate: $hydrate,
        );
    }

    /**
     * Returns these options with entity metadata available for property-to-column mapping.
     *
     * @param class-string $entityClass
     * @throws ORMException
     */
    public function forEntity(string $entityClass): self
    {
        if ($this->entityClass === $entityClass) {
            return $this;
        }

        return new self(
            conditions: $this->conditions,
            exclude: $this->excludeIsExplicit ? $this->exclude : null,
            entityClass: $entityClass,
            withRealTotal: $this->withRealTotal,
            hydrate: $this->hydrate,
        );
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    public function __toString(): string
    {
        return $this->buildConditionString(
            dialect: SQLDialect::MYSQL,
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
     * @param SQLDialect $dialect
     * @param callable(string, mixed): string $renderer
     * @return string
     */
    private function buildConditionString(SQLDialect $dialect, callable $renderer): string
    {
        $parts = [];

        foreach ($this->conditions as $key => $value) {
            $parts[] = $renderer(
                $this->qualifyColumnName($this->resolveColumnName((string)$key), $dialect),
                $value
            );
        }

        return implode(' AND ', $parts);
    }

    /**
     * @param string $columnName
     * @param SQLDialect $dialect
     * @return string
     */
    private function qualifyColumnName(string $columnName, SQLDialect $dialect = SQLDialect::MYSQL): string
    {
        $qualifiedColumn = $this->tableName
            ? "{$this->tableName}.{$columnName}"
            : $columnName;

        return SqlIdentifier::quote($qualifiedColumn, $dialect);
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
            dialect: $query->getDialect(),
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
