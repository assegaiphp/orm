<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Enumerations\SQLDialect;
use Stringable;

class SQLColumnDefinition implements Stringable
{
  /**
   * @var string
   */
  private string $queryString;

  /**
   * @param string $name
   * @param ColumnType $type
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
    $this->queryString = trim(match ($this->dialect) {
      SQLDialect::POSTGRESQL => $this->buildPostgreSqlDefinition(),
      SQLDialect::SQLITE => $this->buildSqliteDefinition(),
      default => $this->buildMySqlDefinition(),
    });
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

  public function getTypeExpression(): string
  {
    return match ($this->dialect) {
      SQLDialect::POSTGRESQL => $this->getPostgreSqlType(),
      SQLDialect::SQLITE => $this->getSqliteType(),
      default => $this->type->value,
    };
  }

  public function getDefaultExpression(): ?string
  {
    if (is_null($this->defaultValue)) {
      return null;
    }

    return $this->normalizeDefaultValue($this->defaultValue);
  }

  public function isNullable(): bool
  {
    return $this->nullable;
  }

  public function isAutoIncrement(): bool
  {
    return $this->autoIncrement;
  }

  public function isUnique(): bool
  {
    return $this->isUnique;
  }

  public function isPrimaryKey(): bool
  {
    return $this->isPrimaryKey;
  }

  private function buildMySqlDefinition(): string
  {
    $queryString = $this->getQuotedColumnName() . ' ';
    $lengthOrValues = $this->getNormalizedLengthOrValues();

    if (!is_null($lengthOrValues)) {
      switch ($this->type) {
        case ColumnType::TINYINT:
        case ColumnType::SMALLINT:
        case ColumnType::INT:
        case ColumnType::BIGINT:
          $queryString .= $this->type->value . (!empty($lengthOrValues) ? "($lengthOrValues) " : ' ');
          break;
        case ColumnType::TINYINT_UNSIGNED:
        case ColumnType::SMALLINT_UNSIGNED:
        case ColumnType::INT_UNSIGNED:
        case ColumnType::BIGINT_UNSIGNED:
          $queryString .= $this->type->value;
          $queryString = !empty($lengthOrValues)
            ? str_replace(' UNSIGNED', "($lengthOrValues) UNSIGNED ", $queryString)
            : $queryString . ' ';
          break;
        case ColumnType::VARCHAR:
          $queryString .= $this->type->value . "($lengthOrValues) ";
          break;
        case ColumnType::ENUM:
          $queryString .= $this->buildEnumTypeDefinition();
          break;
        default:
          $queryString .= "{$this->type->value} ";
      }
    } else {
      $queryString .= "{$this->type->value} ";
    }

    $queryString .= $this->buildDefaultClause();

    if ($this->autoIncrement && $this->type->isNumeric()) {
      $queryString .= 'AUTO_INCREMENT ';
    }

    $queryString .= $this->nullable && !$this->isPrimaryKey ? 'NULL ' : 'NOT NULL ';

    if ($this->isPrimaryKey) {
      $queryString .= 'PRIMARY KEY ';
    } elseif ($this->isUnique) {
      $queryString .= trim("UNIQUE {$this->uniqueKey}") . ' ';
    }

    if (!empty($this->onUpdate)) {
      $queryString .= 'ON UPDATE CURRENT_TIMESTAMP ';
    }

    if (!empty($this->comment)) {
      $queryString .= "COMMENT {$this->comment} ";
    }

    return $this->normalizeQueryString($queryString);
  }

  private function buildSqliteDefinition(): string
  {
    $queryString = $this->getQuotedColumnName() . ' ';

    if ($this->autoIncrement && $this->isPrimaryKey && $this->type->isNumeric()) {
      return $queryString . 'INTEGER PRIMARY KEY AUTOINCREMENT';
    }

    $queryString .= $this->getSqliteType() . ' ';
    $queryString .= $this->buildDefaultClause();

    if (!$this->nullable && !$this->isPrimaryKey) {
      $queryString .= 'NOT NULL ';
    }

    if ($this->isPrimaryKey) {
      $queryString .= 'PRIMARY KEY ';
    } elseif ($this->isUnique) {
      $queryString .= 'UNIQUE ';
    }

    return $this->normalizeQueryString($queryString);
  }

  private function buildPostgreSqlDefinition(): string
  {
    $queryString = $this->getQuotedColumnName() . ' ';

    if ($this->autoIncrement && $this->isPrimaryKey && $this->type->isNumeric()) {
      $type = in_array($this->type, [ColumnType::BIGINT, ColumnType::BIGINT_UNSIGNED], true) ? 'BIGSERIAL' : 'SERIAL';
      return $queryString . "$type PRIMARY KEY";
    }

    $queryString .= $this->getPostgreSqlType() . ' ';
    $queryString .= $this->buildDefaultClause();

    if (!$this->nullable && !$this->isPrimaryKey) {
      $queryString .= 'NOT NULL ';
    }

    if ($this->isPrimaryKey) {
      $queryString .= 'PRIMARY KEY ';
    } elseif ($this->isUnique) {
      $queryString .= 'UNIQUE ';
    }

    return $this->normalizeQueryString($queryString);
  }

  private function getQuotedColumnName(): string
  {
    if (empty($this->name)) {
      return '';
    }

    return match ($this->dialect) {
      SQLDialect::POSTGRESQL => "\"{$this->name}\"",
      default => "`{$this->name}`",
    };
  }

  private function getNormalizedLengthOrValues(): null|string|int|array
  {
    if (is_string($this->lengthOrValues)) {
      $trimmedLength = trim($this->lengthOrValues);

      if ($trimmedLength !== '') {
        return $trimmedLength;
      }
    } elseif (!is_null($this->lengthOrValues)) {
      return $this->lengthOrValues;
    }

    return match ($this->type) {
      ColumnType::VARCHAR => '255',
      ColumnType::DECIMAL => '16,2',
      default => null,
    };
  }

  private function buildEnumTypeDefinition(): string
  {
    $values = is_array($this->lengthOrValues) ? $this->lengthOrValues : [];
    $queryString = $this->type->value . '(';

    foreach ($values as $value) {
      $queryString .= "'$value', ";
    }

    return trim($queryString, ', ') . ') ';
  }

  private function buildDefaultClause(): string
  {
    $defaultValue = $this->defaultValue;

    if ($this->type->isNumeric() && is_string($defaultValue)) {
      $defaultValue = $this->nullable || $this->autoIncrement ? null : 0;
    }

    if (is_null($defaultValue)) {
      return '';
    }

    return 'DEFAULT ' . $this->normalizeDefaultValue($defaultValue) . ' ';
  }

  private function normalizeDefaultValue(mixed $defaultValue): string
  {
    $stringExemptions = ['CURRENT_TIMESTAMP', 'CURRENT_DATE()', 'CURRENT_TIME()', 'JSON_ARRAY()'];

    return match (gettype($defaultValue)) {
      'object' => method_exists($defaultValue, '__toString') ? strval($defaultValue) : json_encode($defaultValue),
      'boolean' => (string)intval($defaultValue),
      'string' => !in_array($defaultValue, $stringExemptions, true)
        ? "'" . $defaultValue . "'"
        : match ($defaultValue) {
          Column::CURRENT_DATE => $this->dialect === SQLDialect::POSTGRESQL ? 'CURRENT_DATE' : 'CURRENT_DATE',
          Column::CURRENT_TIME => $this->dialect === SQLDialect::POSTGRESQL ? 'CURRENT_TIME' : 'CURRENT_TIME',
          default => $defaultValue,
        },
      default => (string)$defaultValue,
    };
  }

  private function getSqliteType(): string
  {
    return match (true) {
      in_array($this->type, [
        ColumnType::BOOLEAN,
        ColumnType::TINYINT,
        ColumnType::TINYINT_UNSIGNED,
        ColumnType::SMALLINT,
        ColumnType::SMALLINT_UNSIGNED,
        ColumnType::MEDIUMINT,
        ColumnType::MEDIUMINT_UNSIGNED,
        ColumnType::INT,
        ColumnType::INT_UNSIGNED,
        ColumnType::BIGINT,
        ColumnType::BIGINT_UNSIGNED,
        ColumnType::BIT,
      ], true) => 'INTEGER',
      in_array($this->type, [ColumnType::FLOAT, ColumnType::DOUBLE, ColumnType::DECIMAL], true) => 'REAL',
      in_array($this->type, [
        ColumnType::BINARY,
        ColumnType::BLOB,
        ColumnType::MEDIUMBLOB,
        ColumnType::LONGBLOB,
        ColumnType::TINYBLOB,
        ColumnType::VARBINARY,
      ], true) => 'BLOB',
      $this->type->isDateTime() => 'DATETIME',
      default => 'TEXT',
    };
  }

  private function getPostgreSqlType(): string
  {
    return match ($this->type) {
      ColumnType::BOOLEAN => 'BOOLEAN',
      ColumnType::TINYINT,
      ColumnType::SMALLINT,
      ColumnType::SMALLINT_UNSIGNED => 'SMALLINT',
      ColumnType::INT,
      ColumnType::INT_UNSIGNED,
      ColumnType::MEDIUMINT,
      ColumnType::MEDIUMINT_UNSIGNED => 'INTEGER',
      ColumnType::BIGINT,
      ColumnType::BIGINT_UNSIGNED => 'BIGINT',
      ColumnType::FLOAT => 'REAL',
      ColumnType::DOUBLE,
      ColumnType::DECIMAL => 'DOUBLE PRECISION',
      ColumnType::JSON => 'JSONB',
      ColumnType::UUID => 'UUID',
      ColumnType::DATE => 'DATE',
      ColumnType::TIME => 'TIME',
      ColumnType::DATETIME,
      ColumnType::TIMESTAMP => 'TIMESTAMP',
      ColumnType::BINARY,
      ColumnType::BLOB,
      ColumnType::MEDIUMBLOB,
      ColumnType::LONGBLOB,
      ColumnType::TINYBLOB,
      ColumnType::VARBINARY => 'BYTEA',
      ColumnType::VARCHAR => 'VARCHAR(' . ($this->getNormalizedLengthOrValues() ?: 255) . ')',
      default => 'TEXT',
    };
  }

  private function normalizeQueryString(string $queryString): string
  {
    $queryString = str_replace('((', '(', $queryString);
    return str_replace('))', ')', $queryString);
  }
}
