<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Represents a raw SQL expression that should be rendered directly by a query builder.
 */
final readonly class SQLExpression
{
    public function __construct(private string $expression)
    {
    }

    public function __toString(): string
    {
        return $this->expression;
    }
}
