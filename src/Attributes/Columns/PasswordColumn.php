<?php

namespace Assegai\Orm\Attributes\Columns;

use Assegai\Core\Config;
use Assegai\Orm\Queries\Sql\ColumnType;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class PasswordColumn extends Column
{
  private mixed $passwordHashAlgorithm;
  public function __construct(
    public string $name = 'password',
    public string $alias = '',
    public string $comment = '',
  )
  {
    $this->passwordHashAlgorithm = Config::get('default_password_hash_algo');

    if (empty($this->passwordHashAlgorithm))
    {
      $this->passwordHashAlgorithm = PASSWORD_DEFAULT;
    }

    parent::__construct(
      name: $name,
      alias: $alias,
      type: ColumnType::TEXT,
      default: password_hash('password', $this->passwordHashAlgorithm),
      comment: $comment
    );
  }
}
