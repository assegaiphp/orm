<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Assegai\Orm\Attributes\Columns;

use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Queries\Sql\SQLColumnDefinition;
use Assegai\Orm\Queries\Sql\ColumnType;
use Attribute;
use UnitEnum;

/**
 * Since database tables consist of columns your entities must consist of columns too. Each entity class property
 * you marked with #[Column()] will be mapped to a database table column.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
  // TODO: Move this constants to an appropriate class/enum
  const SIGNED = 'SIGNED';
  const UNSIGNED = 'UNSIGNED';
  const ZEROFILL = 'ZEROFILL';
  const NOW = 'NOW';
  const CURRENT_DATE = 'CURRENT_DATE()';
  const CURRENT_TIME = 'CURRENT_TIME()';
  const CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
  const DEFAULT_LENGTH_CHAR = 30;
  const DEFAULT_LENGTH_VARCHAR = 255;

  public string $value;
  public SQLColumnDefinition|string $sqlDefinition = '';

  /**
   * @param string $name Column name in the database table. By default, the column name is generated from the name of
   * the property. You can change it by specifying your own name.
   * @param string $alias
   * @param string $type Column type.
   * @param string|array|int|null $lengthOrValues Column type's length. For example if you want to create varchar(150)
   * type you specify column type and length options.
   * @param bool $nullable Makes column NULL or NOT NULL in the database. By default, column is nullable: false.
   * @param bool $unsigned Puts UNSIGNED attribute on to a numeric column. Used only in MySQL.
   * @param bool $zeroFilled Puts ZEROFILL attribute on to a numeric column. Used only in MySQL. If true, MySQL
   * automatically adds the UNSIGNED attribute to this column.
   * @param mixed|null $default
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
    public string                $type = ColumnType::INT,
    public null|string|array|int $lengthOrValues = null,
    public bool                  $nullable = true,
    public bool                  $unsigned = false,
    public bool                  $zeroFilled = false,
    public mixed                 $default = null,
    public bool                  $autoIncrement = false,
    public string                $onUpdate = '',
    public bool                  $isUnique = false,
    public string                $uniqueKey = '',
    public bool                  $isPrimaryKey = false,
    public string                $comment = '',
    public bool                  $canUpdate = true,
    public string|UnitEnum       $enum = ''
  )
  {
    # Build definition string
    if ($this->type === ColumnType::ENUM && !empty($this->enum))
    {
      if (enum_exists($this->enum))
      {
        $this->lengthOrValues = [];
        /** @var UnitEnum $enum */
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
        ColumnType::VARCHAR => strval(self::DEFAULT_LENGTH_VARCHAR),
        ColumnType::DECIMAL => '16,2',
        default => null
      };
    }

    $this->sqlDefinition = new SQLColumnDefinition(
      name: $this->name,
      type: $this->type,
      lengthOrValues: $sqlLengthOrValues,
      defaultValue: $this->default,
      nullable: $this->nullable,
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

    if ($unsigned)                 { $this->value .= Column::UNSIGNED . ' '; }
    if (!$nullable)              { $this->value .= 'NOT '; }

    $this->value .= 'NULL ';

    if ($zeroFilled && $unsigned)  { $this->value .= Column::ZEROFILL . ' '; }
    if (isset($this->default))
    {
      if (is_object($this->default) && property_exists($this->default, 'value'))
      {
        $this->default = $this->default->value;
      }
      else if(is_callable($this->default))
      {
        $this->default = call_user_func($this->default);
      }

      $this->value .= "DEFAULT $this->default ";
    }

    if ($autoIncrement)           { $this->value .= "AUTO_INCREMENT "; }
    if ($isUnique)                { $this->value .= "UNIQUE $uniqueKey"; }

    if (isset($alias))            { $this->value .= "AS $alias"; }

    $this->value = trim($this->value);
  }
}
