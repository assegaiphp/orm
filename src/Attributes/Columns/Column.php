<?php

namespace Assegai\Orm\Attributes\Columns;

use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Queries\Sql\SQLColumnDefinition;
use Assegai\Orm\Queries\Sql\SQLDataTypes;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
  const SIGNED = 'SIGNED';
  const UNSIGNED = 'UNSIGNED';
  const ZEROFILL = 'ZEROFILL';
  const NOW = 'NOW';
  const CURRENT_DATE = 'CURRENT_DATE()';
  const CURRENT_TIME = 'CURRENT_TIME()';
  const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';

  public string $value;
  public string $sqlDefinition = '';

  /**
   * @param string $name
   * @param string $alias
   * @param string $type
   * @param string|array|int|null $lengthOrValues
   * @param bool $allowNull
   * @param bool $signed
   * @param bool $zeroFilled
   * @param mixed|null $defaultValue
   * @param bool $autoIncrement
   * @param string $onUpdate
   * @param bool $isUnique
   * @param string $uniqueKey
   * @param bool $isPrimaryKey
   * @param string $comment
   * @param bool $canUpdate
   * @param string $enum
   * @throws ORMException
   */
  public function __construct(
    public string                $name = '',
    public string                $alias = '',
    public string                $type = SQLDataTypes::INT,
    public null|string|array|int $lengthOrValues = null,
    public bool                  $allowNull = true,
    public bool                  $signed = true,
    public bool                  $zeroFilled = false,
    public mixed                 $defaultValue = null,
    public bool                  $autoIncrement = false,
    public string                $onUpdate = '',
    public bool                  $isUnique = false,
    public string                $uniqueKey = '',
    public bool                  $isPrimaryKey = false,
    public string                $comment = '',
    public bool                  $canUpdate = true,
    public string                $enum = ''
  )
  {
    # Build definition string
    if ($this->type === SQLDataTypes::ENUM && !empty($this->enum))
    {
      if (enum_exists($this->enum))
      {
        $this->lengthOrValues = [];
        /** @var array $cases */
        $cases = $this->enum::cases();

        foreach ($cases as $case)
        {
          if (!isset($case->value))
          {
            throw new ORMException('Enum ' . $this->enum . ' is NOT backed.');
          }
          $this->lengthOrValues[] = $case->value;
        }
      }
    }

    $sqlLengthOrValues = $this->lengthOrValues;
    if (is_null($sqlLengthOrValues))
    {
      $sqlLengthOrValues = match ($this->type) {
        SQLDataTypes::VARCHAR => '10',
        SQLDataTypes::DECIMAL => '16,2',
        default => null
      };
    }

    $this->sqlDefinition = new SQLColumnDefinition(
      name: $this->name,
      dataType: $this->type,
      lengthOrValues: $sqlLengthOrValues,
      defaultValue: $this->defaultValue,
      allowNull: $this->allowNull,
      autoIncrement: $this->autoIncrement,
      onUpdate: $this->onUpdate,
      isUnique: $this->isUnique,
      isPrimaryKey: $this->isPrimaryKey,
      comment: $this->comment
    );

    $this->lengthOrValues = match(gettype($this->lengthOrValues)) {
      'array' => empty($this->lengthOrValues) ? '' : '(' . implode(',', $this->lengthOrValues) . ')',
      'NULL'  => '',
      default => empty($this->lengthOrValues) ? '' : '(' . $this->lengthOrValues  . ')'
    };

    $this->value = "$type$this->lengthOrValues ";

    if (!$signed)                 { $this->value .= Column::UNSIGNED . ' '; }
    if (!$allowNull)              { $this->value .= 'NOT '; }

    $this->value .= 'NULL ';

    if ($zeroFilled && !$signed)  { $this->value .= Column::ZEROFILL . ' '; }
    if (isset($this->defaultValue))
    {
      if (is_object($this->defaultValue) && property_exists($this->defaultValue, 'value'))
      {
        $this->defaultValue = $this->defaultValue->value;
      }
      else if(is_callable($this->defaultValue))
      {
        $this->defaultValue = call_user_func($this->defaultValue);
      }

      $this->value .= "DEFAULT $this->defaultValue ";
    }

    if ($autoIncrement)           { $this->value .= "AUTO_INCREMENT "; }
    if ($isUnique)                { $this->value .= "UNIQUE $uniqueKey"; }

    if (isset($alias))            { $this->value .= "AS $alias"; }

    $this->value = trim($this->value);
  }
}
