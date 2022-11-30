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
      type: ColumnType::BIGINT_UNSIGNED,
      nullable: false,
      autoIncrement: true,
      isPrimaryKey: true,
      comment: $comment,
    );
  }
}