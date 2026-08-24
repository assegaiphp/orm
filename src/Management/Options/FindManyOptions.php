<?php

namespace Assegai\Orm\Management\Options;

use Assegai\Orm\Exceptions\ORMException;

/**
 * Defines the search criteria for finding many entities.
 */
class FindManyOptions extends FindOptions
{
    /**
     * @param int|null $skip
     * @param int|null $limit
     * @param array|null $exclude
     */
    public function __construct(
        ?int   $skip = null,
        ?int   $limit = null,
        ?array $exclude = null,
    )
    {
        parent::__construct(skip: $skip, limit: $limit, exclude: $exclude);
    }

    /**
     * @param array $options
     * @return FindOptions
     * @throws ORMException
     */
    public static function fromArray(array $options): FindOptions
    {
        $excludeIsExplicit = $options['exclude_explicit'] ?? array_key_exists('exclude', $options);

        return new FindManyOptions(
            skip: $options['skip'] ?? null,
            limit: $options['limit'] ?? null,
            exclude: $excludeIsExplicit ? ($options['exclude'] ?? []) : null,
        );
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "LIMIT {$this->limit} OFFSET {$this->skip}";
    }
}
