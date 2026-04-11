<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Marks DROP builders that also support database-level administration.
 *
 * This remains a marker because dialect-specific database drop signatures
 * differ meaningfully across the SQL family.
 */
interface SQLDatabaseDropDefinitionInterface extends SQLDropDefinitionInterface
{
}
