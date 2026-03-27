<?php

namespace Assegai\Orm\Management\Options;

/**
 * Options for updating an entity.
 *
 * @package Assegai\Orm\Management\Options
 */
readonly class UpdateOptions
{
    /**
     * @var object|array|string[]|null $relations
     */
    public object|array|null $relations;

    /**
     * UpdateOptions constructor.
     *
     * @param object|string[]|null $relations The relations to include.
     * @param bool $isDebug Whether to output debug information.
     */
    public function __construct(
        object|array|null $relations = null,
        public bool       $isDebug = false,
        public ?array     $readonlyColumns = null,
        public string     $primaryKeyField = 'id',
    )
    {
        $this->relations = $this->normalizeRelations($relations);
    }

    /**
     * @return string[]
     */
    private function normalizeRelations(object|array|null $relations): array
    {
        if (is_null($relations)) {
            return [];
        }

        if (is_object($relations)) {
            $relations = (array)$relations;
        }

        if (!array_is_list($relations)) {
            $normalizedRelations = [];

            foreach ($relations as $relation => $enabled) {
                if (!is_string($relation)) {
                    throw new \InvalidArgumentException("Each relation key must be of type string");
                }

                if ($enabled) {
                    $normalizedRelations[] = trim($relation);
                }
            }

            return $normalizedRelations;
        }

        return array_map(
            fn($relation) => (is_string($relation) ? trim($relation) : throw new \InvalidArgumentException("Each relation must be of type string")),
            $relations
        );
    }
}
