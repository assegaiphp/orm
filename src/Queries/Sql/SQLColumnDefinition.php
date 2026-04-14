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
    $this->queryString = trim($this->buildDefinition());
  }

  /**
   * Create a dialect-specific column-definition builder.
   *
   * Internal ORM code should prefer this factory so dialect-specific builders
   * can evolve without forcing callers back through the generic SQL namespace.
   *
   * @param string $name The column name.
   * @param ColumnType $type The column type.
   * @param string|int|array|null $lengthOrValues The length, precision, or enum values.
   * @param mixed|null $defaultValue The default value expression.
   * @param bool $nullable Whether the column allows NULL.
   * @param bool $autoIncrement Whether the column auto-increments.
   * @param string $onUpdate Optional ON UPDATE expression.
   * @param bool $isUnique Whether the column is unique.
   * @param string $uniqueKey Optional unique-key name.
   * @param bool $isPrimaryKey Whether the column is a primary key.
   * @param string $comment Optional column comment.
   * @param SQLDialect $dialect The SQL dialect to target.
   * @return SQLColumnDefinition Returns the dialect-specific column-definition builder.
   */
  public static function forDialect(
    string $name,
    ColumnType $type = ColumnType::INT,
    null|string|int|array $lengthOrValues = null,
    mixed $defaultValue = null,
    bool $nullable = true,
    bool $autoIncrement = false,
    string $onUpdate = "",
    bool $isUnique = false,
    string $uniqueKey = "",
    bool $isPrimaryKey = false,
    string $comment = "",
    SQLDialect $dialect = SQLDialect::MYSQL,
  ): SQLColumnDefinition
  {
    return match ($dialect) {
      SQLDialect::POSTGRESQL => new \Assegai\Orm\Queries\PostgreSql\PostgreSQLColumnDefinition(
        name: $name,
        type: $type,
        lengthOrValues: $lengthOrValues,
        defaultValue: $defaultValue,
        nullable: $nullable,
        autoIncrement: $autoIncrement,
        onUpdate: $onUpdate,
        isUnique: $isUnique,
        uniqueKey: $uniqueKey,
        isPrimaryKey: $isPrimaryKey,
        comment: $comment,
      ),
      SQLDialect::SQLITE => new \Assegai\Orm\Queries\SQLite\SQLiteColumnDefinition(
        name: $name,
        type: $type,
        lengthOrValues: $lengthOrValues,
        defaultValue: $defaultValue,
        nullable: $nullable,
        autoIncrement: $autoIncrement,
        onUpdate: $onUpdate,
        isUnique: $isUnique,
        uniqueKey: $uniqueKey,
        isPrimaryKey: $isPrimaryKey,
        comment: $comment,
      ),
      SQLDialect::MARIADB => new \Assegai\Orm\Queries\MariaDb\MariaDbColumnDefinition(
        name: $name,
        type: $type,
        lengthOrValues: $lengthOrValues,
        defaultValue: $defaultValue,
        nullable: $nullable,
        autoIncrement: $autoIncrement,
        onUpdate: $onUpdate,
        isUnique: $isUnique,
        uniqueKey: $uniqueKey,
        isPrimaryKey: $isPrimaryKey,
        comment: $comment,
      ),
      default => new \Assegai\Orm\Queries\MySql\MySQLColumnDefinition(
        name: $name,
        type: $type,
        lengthOrValues: $lengthOrValues,
        defaultValue: $defaultValue,
        nullable: $nullable,
        autoIncrement: $autoIncrement,
        onUpdate: $onUpdate,
        isUnique: $isUnique,
        uniqueKey: $uniqueKey,
        isPrimaryKey: $isPrimaryKey,
        comment: $comment,
      ),
    };
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
    return $this->resolveTypeExpression();
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

  /**
   * Build the rendered SQL column definition for the active builder.
   *
   * Subclasses override this to own the top-level rendering decision while
   * still reusing the shared helper methods below.
   *
   * @return string Returns the rendered SQL column definition.
   */
  protected function buildDefinition(): string
  {
    return $this->buildMySqlDefinition();
  }

  /**
   * Resolve the SQL type expression used by this column-definition builder.
   *
   * Subclasses override this when the target dialect needs a different type
   * mapping strategy from the default SQL-family behavior.
   *
   * @return string Returns the SQL type expression.
   */
  protected function resolveTypeExpression(): string
  {
    return $this->type->value;
  }

  protected function buildMySqlDefinition(): string
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

  protected function buildSqliteDefinition(): string
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

  protected function buildPostgreSqlDefinition(): string
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

  protected function getQuotedColumnName(): string
  {
    if (empty($this->name)) {
      return '';
    }

    return match ($this->dialect) {
      SQLDialect::POSTGRESQL => "\"{$this->name}\"",
      default => "`{$this->name}`",
    };
  }

  protected function getNormalizedLengthOrValues(): null|string|int|array
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

  protected function buildEnumTypeDefinition(): string
  {
    $values = is_array($this->lengthOrValues) ? $this->lengthOrValues : [];
    $queryString = $this->type->value . '(';

    foreach ($values as $value) {
      $queryString .= "'$value', ";
    }

    return trim($queryString, ', ') . ') ';
  }

  protected function buildDefaultClause(): string
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

  protected function normalizeDefaultValue(mixed $defaultValue): string
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

  protected function getSqliteType(): string
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

  protected function getPostgreSqlType(): string
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

  protected function normalizeQueryString(string $queryString): string
  {
    $queryString = str_replace('((', '(', $queryString);
    return str_replace('))', ')', $queryString);
  }
}
