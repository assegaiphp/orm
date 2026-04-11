<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLUpdateDefinition;

/**
 * MySQL-specific UPDATE builder.
 */
class MySQLUpdateDefinition extends SQLUpdateDefinition
{
    /**
     * Create a new MySQL UPDATE statement builder.
     *
     * @param SQLQuery $query The query instance being built.
     * @param string $tableName The target table name.
     * @param bool $lowPriority Whether the UPDATE should use LOW_PRIORITY.
     * @param bool $ignore Whether the UPDATE should use IGNORE.
     */
    public function __construct(
        protected SQLQuery $query,
        protected string $tableName,
        private readonly bool $lowPriority = false,
        private readonly bool $ignore = false,
    )
    {
        $queryString = 'UPDATE ';

        if ($this->lowPriority) {
            $queryString .= 'LOW_PRIORITY ';
        }

        if ($this->ignore) {
            $queryString .= 'IGNORE ';
        }

        $this->query->setQueryString(trim($queryString) . ' ' . $this->query->quoteIdentifier($this->tableName));
    }
}