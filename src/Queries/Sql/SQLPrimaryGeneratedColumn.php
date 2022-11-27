<?php

namespace Assegai\Orm\Queries\Sql;

final class SQLPrimaryGeneratedColumn extends SQLColumnDefinition
{
  /**
   * @param string $name
   * @param string $comment
   */
  public function __construct(
    private readonly string $name = 'id',
    private readonly string $comment = ""
  )
  {
    parent::__construct(
      name: $name,
      dataType: ColumnType::BIGINT_UNSIGNED,
      allowNull: false,
      autoIncrement: true,
      isPrimaryKey: true,
      comment: $comment,
    );
  }
}