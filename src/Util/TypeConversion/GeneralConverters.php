<?php

namespace Assegai\Orm\Util\TypeConversion;

use Assegai\Orm\Attributes\TypeConverter;
use DateTime;
use Exception;

/**
 * Defines methods for basic type conversion.
 */
class GeneralConverters
{
  /**
   * @param DateTime $dateTime
   * @return string
   */
  #[TypeConverter]
  public function fromDateTimeToString(DateTime $dateTime): string
  {
    return $dateTime->format(DATE_ATOM);
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