<?php

namespace Assegai\Orm\Enumerations;

/**
 * Enumeration class for DatabaseObjectType.
 * This enumeration defines constants representing types of database objects.
 */
enum DatabaseObjectType: string
{
  /**
   * The type of database table.
   */
  case TABLE = 'TABLE';

  /**
   * The type of database view.
   */
  case VIEW = 'VIEW';

  /**
   * The type of database index.
   */
  case INDEX = 'INDEX';

  /**
   * The type of stored procedure.
   */
  case STORED_PROCEDURE = 'STORED PROCEDURE';

  /**
   * The type of stored function.
   */
  case STORED_FUNCTION = 'STORED_FUNCTION';

  /**
   * The type of database trigger.
   */
  case TRIGGER = 'TRIGGER';

  /**
   * The type of database event.
   */
  case EVENT = 'EVENT';

  /**
   * The type of user-defined function.
   */
  case USER_DEFINED_FUNCTION = 'UDF';

  /**
   * The type of database server.
   */
  case SERVER = 'SERVER';

  /**
   * The type of tablespace.
   */
  case TABLESPACE = 'TABLESPACE';
}
