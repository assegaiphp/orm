<?php

namespace Assegai\Orm\Util\TypeConversion;

use Assegai\Orm\Attributes\TypeConverter;
use DateTime;
use Exception;

/**
 * Defines methods for basic type conversion.
 */
class BasicTypeConverter
{
  public const DEFAULT_DATE_FORMAT = 'Y-m-d\TH:i:s';

  /**
   * @param DateTime $dateTime
   * @return string
   */
  #[TypeConverter]
  public function fromDateTimeToString(DateTime $dateTime): string
  {
    $format = $_ENV['DATETIME_FORMAT'] ?? self::DEFAULT_DATE_FORMAT;
    return $dateTime->format($format);
  }

  /**
   * @param string $dateTime
   * @return DateTime
   * @throws Exception
   */
  #[TypeConverter]
  public function fromStringToDateTime(string $dateTime): DateTime
  {
    $dateTime = preg_replace('/(.*)\(.*\)/', "$1", $dateTime);
    return new DateTime($dateTime);
  }

  #[TypeConverter]
  public function fromNullToBool(?string $source): bool
  {
    return boolval($source);
  }
}