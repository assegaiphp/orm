<?php

namespace Assegai\Orm\DataSource;

/**
 * An enumeration of supported SQL character set names and their corresponding values.
 *
 * The values for each member of this enumeration represent the numeric value used to identify the character set in
 * SQL databases, as specified in the SQL standard. For example, the value of `SQLCharacterSet::UTF8` corresponds to
 * the character set named "UTF8", which is commonly used for storing Unicode text.
 *
 * @see https://dev.mysql.com/doc/refman/8.0/en/charset-charsets.html MySQL character sets
 * @see https://www.postgresql.org/docs/current/multibyte.html PostgreSQL character sets
 * @see https://docs.oracle.com/en/database/oracle/oracle-database/19/nls-charset-fundamentals/ Oracle character sets
 * @see https://docs.microsoft.com/en-us/sql/relational-databases/collations/set-or-change-the-column-collation SQL
 * Server character sets
 * @see https://docs.snowflake.com/en/user-guide/character-sets.html Snowflake character sets
 * @since 1.0.0
 */
enum SQLCharacterSet: string
{
  case ARMSCII8 = 'armscii8';
  case ASCII = 'ascii';
  case BIG5 = 'big5';
  case BINARY = 'binary';
  case CP1250 = 'cp1250';
  case CP1251 = 'cp1251';
  case CP1256 = 'cp1256';
  case CP1257 = 'cp1257';
  case CP850 = 'cp850';
  case CP852 = 'cp852';
  case CP866 = 'cp866';
  case CP932 = 'cp932';
  case DEC8 = 'dec8';
  case EUCJPMS = 'eucjpms';
  case EUCKR = 'euckr';
  case GB18030 = 'gb18030';
  case GB2312 = 'gb2312';
  case GBK = 'gbk';
  case GEOSTD8 = 'geostd8';
  case GREEK = 'greek';
  case HEBREW = 'hebrew';
  case HP8 = 'hp8';
  case KEYBCS2 = 'keybcs2';
  case KOI8R = 'koi8r';
  case KOI8U = 'koi8u';
  case LATIN1 = 'latin1';
  case LATIN2 = 'latin2';
  case LATIN5 = 'latin5';
  case LATIN7 = 'latin7';
  case MACCE = 'macce';
  case MACROMAN = 'macroman';
  case SJIS = 'sjis';
  case SWE7 = 'swe7';
  case TIS620 = 'tis620';
  case UCS2 = 'ucs2';
  case UJIS = 'ujis';
  case UTF16 = 'utf16';
  case UTF16LE = 'utf16le';
  case UTF32 = 'utf32';
  case UTF8MB3 = 'utf8mb3';
  case UTF8MB4 = 'utf8mb4';

  /**
   * Returns the default collation for the character set based on its value.
   *
   * @return string The default collation for the character set.
   */
  public function getDefaultCollation(): string
  {
    return match($this) {
      self::ARMSCII8 => 'armscii8_general_ci',
      self::ASCII => 'ascii_general_ci',
      self::BIG5 => 'big5_chinese_ci',
      self::BINARY => 'binary',
      self::CP1250 => 'cp1250_general_ci',
      self::CP1251 => 'cp1251_general_ci',
      self::CP1256 => 'cp1256_general_ci',
      self::CP1257 => 'cp1257_general_ci',
      self::CP850 => 'cp850_general_ci',
      self::CP852 => 'cp852_general_ci',
      self::CP866 => 'cp866_general_ci',
      self::CP932 => 'cp932_japanese_ci',
      self::DEC8 => 'dec8_swedish_ci',
      self::EUCJPMS => 'eucjpms_japanese_ci',
      self::EUCKR => 'euckr_korean_ci',
      self::GB18030 => 'gb18030_chinese_ci',
      self::GB2312 => 'gb2312_chinese_ci',
      self::GBK => 'gbk_chinese_ci',
      self::GEOSTD8 => 'geostd8_general_ci',
      self::GREEK => 'greek_general_ci',
      self::HEBREW => 'hebrew_general_ci',
      self::HP8 => 'hp8_english_ci',
      self::KEYBCS2 => 'keybcs2_general_ci',
      self::KOI8R => 'koi8r_general_ci',
      self::KOI8U => 'koi8u_general_ci',
      self::LATIN1 => 'latin1_swedish_ci',
      self::LATIN2 => 'latin2_general_ci',
      self::LATIN5 => 'latin5_turkish_ci',
      self::LATIN7 => 'latin7_general_ci',
      self::MACCE => 'macce_general_ci',
      self::MACROMAN => 'macroman_general_ci',
      self::SJIS => 'sjis_japanese_ci',
      self::SWE7 => 'swe7_swedish_ci',
      self::TIS620 => 'tis620_thai_ci',
      self::UCS2 => 'ucs2_general_ci',
      self::UJIS => 'ujis_japanese_ci',
      self::UTF16 => 'utf16_general_ci',
      self::UTF16LE => 'utf16le_general_ci',
      self::UTF32 => 'utf32_general_ci',
      self::UTF8MB3 => 'utf8mb3_general_ci',
      self::UTF8MB4 => 'utf8mb4_general_ci',
    };
  }

  /**
   * Returns a string description of the character set.
   *
   * @return string The description of the character set.
   */
  public function getDescription(): string {
    return match ($this) {
      self::ARMSCII8 => 'ARMSCII-8 Armenian',
      self::ASCII => 'US ASCII',
      self::BIG5 => 'Big5 Traditional Chinese',
      self::BINARY => 'Binary pseudo charset',
      self::CP1250 => 'Windows Central European',
      self::CP1251 => 'Windows Cyrillic',
      self::CP1256 => 'Windows Arabic',
      self::CP1257 => 'Windows Baltic',
      self::CP850 => 'DOS West European',
      self::CP852 => 'DOS Central European',
      self::CP866 => 'DOS Russian',
      self::CP932 => 'SJIS for Windows Japanese',
      self::DEC8 => 'DEC West European',
      self::EUCJPMS => 'UJIS for Windows Japanese',
      self::EUCKR => 'EUC-KR Korean',
      self::GB18030 => 'China National Standard GB18030',
      self::GB2312 => 'GB2312 Simplified Chinese',
      self::GBK => 'GBK Simplified Chinese',
      self::GEOSTD8 => 'GEOSTD8 Georgian',
      self::GREEK => 'ISO 8859-7 Greek',
      self::HEBREW => 'ISO 8859-8 Hebrew',
      self::HP8 => 'HP West European',
      self::KEYBCS2 => 'DOS Kamenicky Czech-Slovak',
      self::KOI8R => 'KOI8-R Relcom Russian',
      self::KOI8U => 'KOI8-U Ukrainian',
      self::LATIN1 => 'cp1252 West European',
      self::LATIN2 => 'ISO 8859-2 Central European',
      self::LATIN5 => 'ISO 8859-9 Turkish',
      self::LATIN7 => 'ISO 8859-13 Baltic',
      self::MACCE => 'Mac Central European',
      self::MACROMAN => 'Mac West European',
      self::SJIS => 'Shift-JIS Japanese',
      self::SWE7 => '7bit Swedish',
      self::TIS620 => 'TIS620 Thai',
      self::UCS2 => 'UCS-2 Unicode',
      self::UJIS => 'EUC-JP Japanese',
      self::UTF16 => 'UTF-16 Unicode',
      self::UTF16LE => 'UTF-16LE Unicode',
      self::UTF32 => 'UTF-32 Unicode',
      self::UTF8MB3,
      self::UTF8MB4 => 'UTF-8 Unicode',
    };
  }

  /**
   * Returns the maximum number of bytes required to store one character.
   * This method calculates the maximum number of bytes that may be required to store a single character in the
   * encoding used by this instance of the Charset class. The value returned may vary depending on the encoding used,
   * and may be different for different character sets.
   *
   * @return int The maximum number of bytes required to store one character.
   */
  public function getMaxLength(): int {
    return match ($this) {
      self::BIG5,
      self::CP932,
      self::EUCKR,
      self::GB2312,
      self::GBK,
      self::SJIS,
      self::UCS2 => 2,
      self::EUCJPMS,
      self::UJIS => 3,
      self::GB18030,
      self::UTF16,
      self::UTF16LE,
      self::UTF32 => 4,
      default => 1
    };
  }
}
