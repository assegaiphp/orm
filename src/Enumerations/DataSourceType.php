<?php

namespace Assegai\Orm\Enumerations;

enum DataSourceType: string
{
  case MYSQL = 'mysql';
  case MARIADB = 'mariadb';
  case POSTGRESQL = 'pgsql';
  case SQLITE = 'sqlite';
  case MSSQL = 'mssql';
  case MONGODB = 'mongodb';
}