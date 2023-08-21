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
  case FILE = 'file';
  case REDIS = 'redis';
  case MEMCACHED = 'memcached';
  case COUCHDB = 'couchdb';
  case NEO4J = 'neo4j';
  case ELASTICSEARCH = 'elasticsearch';
  case OPENSEARCH = 'opensearch';
}