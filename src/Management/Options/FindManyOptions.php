<?php

namespace Assegai\Orm\Management\Options;

use Assegai\Orm\Exceptions\ORMException;

/**
 * Defines the search criteria for finding many entities.
 */
class FindManyOptions extends FindOneOptions
{
    /**
     * @param int|null $skip
     * @param int|null $limit
     * @param array $exclude
     */
    public function __construct(
        public readonly ?int  $skip = null,
        public readonly ?int  $limit = null,
        public readonly array $exclude = ['password'],
    )
    {
        parent::__construct(skip: $this->skip, limit: $this->limit, exclude: $this->exclude);
    }

    /**
     * @param array $options
     * @return FindOptions
     * @throws ORMException
     */
    public static function fromArray(array $options): FindOptions
    {
        $options = parent::fromArray($options);
        return new FindManyOptions(skip: $options->skip, limit: $options->limit, exclude: $options->exclude);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return "LIMIT {$this->limit} OFFSET {$this->skip}";
    }
}