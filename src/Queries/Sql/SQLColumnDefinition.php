<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Enumerations\SQLDialect;

class SQLColumnDefinition
{
  private string $queryString = "";

  /**
   * @param string $name
   * @param string $dataType
   * @param string|int|array|null $lengthOrValues
   * @param mixed|null $defaultValue
   * @param bool $allowNull
   * @param bool $autoIncrement
   * @param string $onUpdate
   * @param bool $isUnique
   * @param string $uniqueKey
   * @param bool $isPrimaryKey
   * @param string $comment
   * @param SQLDialect $dialect
   */
  public function __construct(
    private readonly string       $name,
    private readonly string       $dataType = DataType::INT,
    private null|string|int|array $lengthOrValues = null,
    private mixed                 $defaultValue = null,
    private readonly bool         $allowNull = true,
    private readonly bool         $autoIncrement = false,
    private readonly string       $onUpdate = "",
    private readonly bool         $isUnique = false,
    private readonly string       $uniqueKey = "",
    private readonly bool         $isPrimaryKey = false,
    private readonly string       $comment = "",
    private readonly SQLDialect   $dialect = SQLDialect::MYSQL,
  )
  {
    $queryString = !empty($this->name) ? "`$this->name` " : '';
    if (is_null($this->lengthOrValues))
    {
      $this->lengthOrValues = match($this->dataType) {
        DataType::VARCHAR => '10',
        DataType::DECIMAL => '16,2',
        default => null
      };
    }

    if (!is_null($this->lengthOrValues))
    {
      switch($this->dataType) {
        case DataType::TINYINT:
        case DataType::SMALLINT:
        case DataType::INT:
        case DataType::BIGINT:
          $queryString .= $this->dataType;
          if (!empty($this->lengthOrValues))
          {
            $queryString .= "(" . $this->lengthOrValues . ") ";
          }
          else
          {
            $queryString .= " ";
          }
          break;
        case DataType::TINYINT_UNSIGNED:
        case DataType::SMALLINT_UNSIGNED:
        case DataType::INT_UNSIGNED:
        case DataType::BIGINT_UNSIGNED:
          $queryString .= $this->dataType;
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
        case DataType::VARCHAR:
          $queryString .= $this->dataType . "(" . $this->lengthOrValues . ") ";

          break;

        case DataType::ENUM:
          if (!is_array($this->lengthOrValues))
          {
            $this->lengthOrValues = [];
          }
          $queryString .= $this->dataType . "(";
          foreach ($this->lengthOrValues as $value)
          {
            $queryString .= "'$value', ";
          }
          $queryString = trim($queryString, ', ');
          $queryString .= ") ";
          break;
  
        default: $queryString .= "$this->dataType ";
      }
    }
    else
    {
      $queryString .= "$this->dataType ";
    }

    if (DataType::isNumeric($this->dataType) && is_string($this->defaultValue))
    {
      $this->defaultValue = $this->allowNull || $this->autoIncrement ? null : 0;
    }

    if (!is_null($this->defaultValue))
    {
      $temporalDatatypes = [
        // SQLDataTypes::DATE,
        DataType::DATETIME
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
    if ($this->autoIncrement && DataType::isNumeric($this->dataType))
    {
      $queryString .= "AUTO_INCREMENT ";
    }
    $queryString .= $this->allowNull && !$this->isPrimaryKey ? "NULL " : "NOT NULL ";
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