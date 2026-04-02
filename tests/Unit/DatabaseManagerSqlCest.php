<?php

namespace Tests\Unit;

use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Management\DatabaseManager;
use Assegai\Orm\Exceptions\DataSourceException;
use Tests\Support\UnitTester;

class DatabaseManagerSqlCest
{
  public function buildsMysqlCreateDatabaseStatementsWithCharsetDefaults(UnitTester $I): void
  {
    $sql = DatabaseManager::buildCreateDatabaseStatement(
      DataSourceType::MYSQL,
      'assegai_blog',
      SQLCharacterSet::UTF8MB4,
    );

    $I->assertSame(
      'CREATE DATABASE IF NOT EXISTS `assegai_blog` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci',
      $sql,
    );
  }

  public function buildsMysqlDropDatabaseStatementsSafely(UnitTester $I): void
  {
    $sql = DatabaseManager::buildDropDatabaseStatement(DataSourceType::MYSQL, 'assegai_blog');

    $I->assertSame('DROP DATABASE IF EXISTS `assegai_blog`', $sql);
  }

  public function rejectsUnsafeDatabaseNames(UnitTester $I): void
  {
    $I->expectThrowable(
      new DataSourceException('Unsafe database name: blog; DROP DATABASE mysql'),
      fn() => DatabaseManager::buildCreateDatabaseStatement(DataSourceType::MYSQL, 'blog; DROP DATABASE mysql')
    );
  }
}
