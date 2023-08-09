<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Enumerations\SQLDialect;
use Stringable;

class SQLColumnDefinition implements Stringable
{
  private string $queryString = "";

  /**
   * @param string $name
   * @param string $type
   * @param string|int|array|null $lengthOrValues
   * @param mixed|null $defaultValue
   * @param bool $nullable
   * @param bool $autoIncrement
   * @param string $onUpdate
   * @param bool $isUnique
   * @param string $uniqueKey
   * @param bool $isPrimaryKey
   * @param string $comment
   * @param SQLDialect $dialect
   */
  public function __construct(
    public readonly string          $name,
    private readonly ColumnType     $type = ColumnType::INT,
    private null|string|int|array   $lengthOrValues = null,
    private mixed                   $defaultValue = null,
    private readonly bool           $nullable = true,
    private readonly bool           $autoIncrement = false,
    private readonly string         $onUpdate = "",
    private readonly bool           $isUnique = false,
    private readonly string         $uniqueKey = "",
    private readonly bool           $isPrimaryKey = false,
    private readonly string         $comment = "",
    private readonly SQLDialect     $dialect = SQLDialect::MYSQL,
  )
  {
    $queryString = !empty($this->name) ? "`$this->name` " : '';
    if (is_null($this->lengthOrValues))
    {
      $this->lengthOrValues = match($this->type) {
        ColumnType::VARCHAR => '10',
        ColumnType::DECIMAL => '16,2',
        default => null
      };
    }

    if (!is_null($this->lengthOrValues))
    {
      switch($this->type) {
        case ColumnType::TINYINT:
        case ColumnType::SMALLINT:
        case ColumnType::INT:
        case ColumnType::BIGINT:
          $queryString .= $this->type;
          if (!empty($this->lengthOrValues))
          {
            $queryString .= "(" . $this->lengthOrValues . ") ";
          }
          else
          {
            $queryString .= " ";
          }
          break;
        case ColumnType::TINYINT_UNSIGNED:
        case ColumnType::SMALLINT_UNSIGNED:
        case ColumnType::INT_UNSIGNED:
        case ColumnType::BIGINT_UNSIGNED:
          $queryString .= $this->type;
          if (!empty($this->lengthOrValues))
          {
            $length = $this->lengthOrValues;
            $queryString = str_replace(' UNSIGNED', "($length) UNSIGNED ", $queryString);
          }
          else
          {
            $queryString .= " ";
          }
          break;
        case ColumnType::VARCHAR:
          $queryString .= $this->type->value . "(" . $this->lengthOrValues . ") ";

          break;

        case ColumnType::ENUM:
          if (!is_array($this->lengthOrValues))
          {
            $this->lengthOrValues = [];
          }
          $queryString .= $this->type->value . "(";
          foreach ($this->lengthOrValues as $value)
          {
            $queryString .= "'$value', ";
          }
          $queryString = trim($queryString, ', ');
          $queryString .= ") ";
          break;
  
        default: $queryString .= "{$this->type->value} ";
      }
    }
    else
    {
      $queryString .= "{$this->type->value} ";
    }

    if ($this->type->isNumeric() && is_string($this->defaultValue))
    {
      $this->defaultValue = $this->nullable || $this->autoIncrement ? null : 0;
    }

    if (!is_null($this->defaultValue))
    {
      $temporalDatatypes = [
        // SQLDataTypes::DATE,
        ColumnType::DATETIME
      ];
      $stringExamptions = ['CURRENT_TIMESTAMP', 'CURRENT_DATE()', 'CURRENT_TIME()', 'JSON_ARRAY()'];
      $queryString .= "DEFAULT " . match(gettype($this->defaultValue)) {
        'object'  => method_exists($this->defaultValue, '__toString') ? strval($this->defaultValue) : json_encode($this->defaultValue),
        'boolean' => intval($this->defaultValue),
        // 'string'  => ( !in_array($this->dataType, $temporalDatatypes) ) ? "'" . $this->defaultValue . "'" : $this->defaultValue,
        'string'  => !in_array($this->defaultValue, $stringExamptions) ? "'" . $this->defaultValue . "'" : (
          match($this->defaultValue) {
            Column::CURRENT_DATE => "'" . date('Y-m-d') . "'",
            Column::CURRENT_TIME => "'" . date('H:i:s') . "'",
            default => $this->defaultValue
          }),
        default   => $this->defaultValue
      } . " ";
    }

    if ($this->autoIncrement && $this->type->isNumeric())
    {
      $queryString .= "AUTO_INCREMENT ";
    }

    $queryString .= $this->nullable && !$this->isPrimaryKey ? "NULL " : "NOT NULL ";

    if ($this->isPrimaryKey)
    {
      $queryString .= "PRIMARY KEY ";
    }
    else if ($this->isUnique)
    {
      $queryString .= trim("UNIQUE " . $this->uniqueKey) . ' ';
    }

    if (!empty($this->onUpdate))
    {
      $queryString .= "ON UPDATE CURRENT_TIMESTAMP ";
    }

    if (!empty($this->comment))
    {
      $queryString .= "COMMENT $this->comment ";
    }

    $queryString = str_replace('((', '(', $queryString);
    $queryString = str_replace('))', ')', $queryString);
    
    $this->queryString = trim($queryString);
  }

  /**
   * @return string
   */
  public function queryString(): string
  {
    return $this->queryString;
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    return $this->queryString();
  }
}