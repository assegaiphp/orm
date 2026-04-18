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
        protected readonly bool $lowPriority = false,
        protected readonly bool $ignore = false,
    )
    {
        parent::__construct(query: $query, tableName: $tableName);
    }

    /**
     * Build the MySQL-specific UPDATE prefix.
     *
     * @return string Returns the leading UPDATE clause with MySQL modifiers when enabled.
     */
    protected function buildUpdatePrefix(): string
    {
        $parts = ['UPDATE'];

        if ($this->lowPriority) {
            $parts[] = 'LOW_PRIORITY';
        }

        if ($this->ignore) {
            $parts[] = 'IGNORE';
        }

        return implode(' ', $parts);
    }

    /**
     * Start building the SET clause and keep the fluent chain on the MySQL builder path.
     *
     * @param array $assignmentList The column/value assignments to apply.
     * @return MySQLAssignmentList Returns the MySQL assignment-list builder.
     */
    public function set(array $assignmentList): MySQLAssignmentList
    {
        return $this->createAssignmentList(assignmentList: $assignmentList);
    }

    /**
     * Create the MySQL assignment-list builder.
     *
     * @param array $assignmentList The column/value assignments to apply.
     * @return MySQLAssignmentList Returns the MySQL assignment-list builder.
     */
    protected function createAssignmentList(array $assignmentList): MySQLAssignmentList
    {
        return new MySQLAssignmentList(query: $this->query, assignmentList: $assignmentList);
    }
}
