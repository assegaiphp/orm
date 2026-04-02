<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\DataSourceType;
use Stringable;

/**
 * Class DataSourceOptions. Represents the options for a DataSource.
 */
readonly class DataSourceOptions implements Stringable
{
  /**
   * @param array $entities T
   * @param string $name
   * @param DataSourceType $type
   * @param string $host
   * @param int $port
   * @param string|null $username
   * @param string|null $password
   * @param bool $synchronize S
   * @param SQLCharacterSet|null $charSet The character set to use.
   * @param string|null $path The path to the database.
   */
  public function __construct(
    public array            $entities,
    public string           $name,
    public DataSourceType   $type = DataSourceType::MYSQL,
    public string           $host = 'localhost',
    public int              $port = 3306,
    public ?string          $username = null,
    public ?string          $password = null,
    public bool             $synchronize = false,
    public ?SQLCharacterSet $charSet = SQLCharacterSet::UTF8MB4,
    public ?string          $path = null,
  )
  {
  }

  /**
   * @return array
   */
  public function toArray(): array
  {
    return [
      'entities' => $this->entities,
      'database' => $this->name,
      'type' => $this->type,
      'host' => $this->host,
      'port' => $this->port,
      'username' => $this->username,
      'password' => $this->password,
      'synchronize' => $this->synchronize,
      'charSet' => $this->charSet?->value,
      'path' => $this->path,
    ];
  }

  /**
   * @param array $props
   * @return static
   */
  public static function fromArray(array $props): self
  {
    $type = $props['type'] ?? DataSourceType::MYSQL;
    if (is_string($type)) {
      $type = DataSourceType::tryFrom($type) ?? DataSourceType::MYSQL;
    }

    $charSet = $props['charSet'] ?? $props['charset'] ?? SQLCharacterSet::UTF8MB4;
    if (is_string($charSet)) {
      $charSet = SQLCharacterSet::tryFrom($charSet) ?? SQLCharacterSet::UTF8MB4;
    }

    return new self(
      entities: $props['entities'] ?? [],
      name: $props['database'] ?? $props['name'] ?? '',
      type: $type,
      host: $props['host'] ?? 'localhost',
      port: $props['port'] ?? 3306,
      username: $props['username'] ?? $props['user'] ?? null,
      password: $props['password'] ?? $props['pass'] ?? null,
      synchronize: $props['synchronize'] ?? false,
      charSet: $charSet,
      path: $props['path'] ?? null,
    );
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    return json_encode($this->toArray());
  }
}
