<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\DataSourceType;
use Stringable;

/**
 *
 */
readonly class DataSourceOptions implements Stringable
{
  /**
   * @param array $entities
   * @param string $database
   * @param DataSourceType $type
   * @param string $host
   * @param int $port
   * @param string|null $username
   * @param string|null $password
   * @param bool $synchronize
   * @param SQLCharacterSet|null $charSet
   */
  public function __construct(
    public array              $entities,
    public string             $database,
    public DataSourceType     $type = DataSourceType::MYSQL,
    public string             $host = 'localhost',
    public int                $port = 3306,
    public ?string            $username = null,
    public ?string            $password = null,
    public bool               $synchronize = false,
    public ?SQLCharacterSet   $charSet = SQLCharacterSet::UTF8MB4
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
      'database' => $this->database,
      'type' => $this->type,
      'host' => $this->host,
      'port' => $this->port,
      'username' => $this->username,
      'password' => $this->password,
      'synchronize' => $this->synchronize
    ];
  }

  /**
   * @param array $props
   * @return static
   */
  public static function fromArray(array $props): self
  {
    return new self(
      entities: $props['entities'] ?? [],
      database: $props['database'] ?? '',
      type: $props['type'] ?? DataSourceType::MYSQL,
      host: $props['host'] ?? 'localhost',
      port: $props['port'] ?? 3306,
      username: $props['username'] ?? ['user'] ?? 'root',
      password: $props['password'] ?? ['pass'] ?? '',
      synchronize: $props['synchronize'] ?? false,
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