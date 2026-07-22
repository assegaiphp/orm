<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\DataSourceType;
use JsonSerializable;
use Stringable;

/**
 * Class DataSourceOptions. Represents the options for a DataSource.
 */
readonly class DataSourceOptions implements JsonSerializable, Stringable
{
  public bool $trustServerCertificate;
  private bool $trustServerCertificateExplicit;

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
   * @param bool|null $trustServerCertificate Whether SQL Server may use an unverified certificate, or null to use runtime configuration.
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
    ?bool                   $trustServerCertificate = null,
  )
  {
    $this->trustServerCertificate = $trustServerCertificate ?? false;
    $this->trustServerCertificateExplicit = $trustServerCertificate !== null;
  }

  public function hasExplicitTrustServerCertificate(): bool
  {
    return $this->trustServerCertificateExplicit;
  }

  /**
   * @return array
   */
  public function toArray(): array
  {
    $options = [
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

    if ($this->hasExplicitTrustServerCertificate()) {
      $options['trustServerCertificate'] = $this->trustServerCertificate;
    }

    return $options;
  }

  /**
   * @return array
   */
  public function toRedactedArray(): array
  {
    $options = $this->toArray();

    if ($options['password'] !== null) {
      $options['password'] = '[REDACTED]';
    }

    return $options;
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

    $trustServerCertificate = match (true) {
      array_key_exists('trustServerCertificate', $props) => self::normalizeBoolean($props['trustServerCertificate']),
      array_key_exists('trust_server_certificate', $props) => self::normalizeBoolean($props['trust_server_certificate']),
      default => null,
    };

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
      trustServerCertificate: $trustServerCertificate,
    );
  }

  public function jsonSerialize(): array
  {
    return $this->toRedactedArray();
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    return json_encode($this->toRedactedArray()) ?: '{}';
  }

  private static function normalizeBoolean(mixed $value): bool
  {
    if (is_bool($value)) {
      return $value;
    }

    return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
  }
}
