<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Marks CREATE builders that also support database-level administration.
 *
 * This remains a marker because dialect-specific database creation signatures
 * differ meaningfully across the SQL family.
 */
interface SQLDatabaseCreateDefinitionInterface extends SQLCreateDefinitionInterface
{
}
