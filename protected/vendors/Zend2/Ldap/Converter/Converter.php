<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Ldap\Converter;

use DateTime;
use DateTimeZone;
use Zend\Stdlib\ErrorHandler;

/**
 * Zend\Ldap\Converter is a collection of useful LDAP related conversion functions.
 */
class Converter
{
    const STANDARD         = 0;
    const BOOLEAN          = 1;
    const GENERALIZED_TIME = 2;

    /**
     * Converts all ASCII chars < 32 to "\HEX"
     *
     * @see    Net_LDAP2_Util::asc2hex32() from Benedikt Hallinger <beni@php.net>
     * @link   http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>
     *
     * @param string $string String to convert
     * @return string
     */
    public static function ascToHex32($string)
    {
        for ($i = 0, $len = strlen($string); $i < $len; $i++) {
            $char = substr($string, $i, 1);
            if (ord($char) < 32) {
                $hex = dechex(ord($char));
                if (strlen($hex) == 1) {
                    $hex = '0' . $hex;
                }
                $string = str_replace($char, '\\' . $hex, $string);
            }
        }
        return $string;
    }

    /**
     * Converts all Hex expressions ("\HEX") to their original ASCII characters
     *
     * @see    Net_LDAP2_Util::hex2asc() from Benedikt Hallinger <beni@php.net>,
     *         heavily based on work from DavidSmith@byu.net
     * @link   http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>, heavily based on work from DavidSmith@byu.net
     *
     * @param string $string String to convert
     * @return string
     */
    public static function hex32ToAsc($string)
    {
        $string = preg_replace_callback('/\\\([0-9A-Fa-f]{2})/', function ($matches) {
            return chr(hexdec($matches[1]));
        }, $string);
        return $string;
    }


    /**
     * Convert any value to an LDAP-compatible value.
     *
     * By setting the <var>$type</var>-parameter the conversion of a certain
     * type can be forced
     *
     * @todo write more tests
     *
     * @param mixed $value The value to convert
     * @param int   $type  The conversion type to use
     * @return string|null
     * @throws Exception\ConverterException
     */
    public static function toLdap($value, $type = self::STANDARD)
    {
        try {
            switch ($type) {
                case self::BOOLEAN:
                    return static::toldapBoolean($value);
                    break;
                case self::GENERALIZED_TIME:
                    return static::toLdapDatetime($value);
                    break;
                default:
                    if (is_string($value)) {
                        return $value;
                    } elseif (is_int($value) || is_float($value)) {
                        return (string) $value;
                    } elseif (is_bool($value)) {
                        return static::toldapBoolean($value);
                    } elseif (is_object($value)) {
                        if ($value instanceof DateTime) {
                            return static::toLdapDatetime($value);
                        } else {
                            return static::toLdapSerialize($value);
                        }
                    } elseif (is_array($value)) {
                        return static::toLdapSerialize($value);
                    } elseif (is_resource($value) && get_resource_type($value) === 'stream') {
                        return stream_get_contents($value);
                    } else {
                        return null;
                    }
                    break;
            }
        } catch (\Exception $e) {
            throw new Exception\ConverterException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Converts a date-entity to an LDAP-compatible date-string
     *
     * The date-entity <var>$date</var> can be either a timestamp, a
     * DateTime Object, a string that is parseable by strtotime().
     *
     * @param int|string|DateTime $date  The date-entity
     * @param  bool                 $asUtc Whether to return the LDAP-compatible date-string as UTC or as local value
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public static function toLdapDateTime($date, $asUtc = true)
    {
        if (!($date instanceof DateTime)) {
            if (is_int($date)) {
                $date = new DateTime('@' . $date);
                $date->setTimezone(new DateTimeZone(date_default_timezone_get()));
            } elseif (is_string($date)) {
                $date = new DateTime($date);
            } else {
                throw new Exception\InvalidArgumentException('Parameter $date is not of the expected type');
            }
        }
        $timezone = $date->format('O');
        if (true === $asUtc) {
            $date->setTimezone(new DateTimeZone('UTC'));
            $timezone = 'Z';
        }
        if ('+0000' === $timezone) {
            $timezone = 'Z';
        }
        return $date->format('YmdHis') . $timezone;
    }

    /**
     * Convert a boolean value to an LDAP-compatible string
     *
     * This converts a boolean value of TRUE, an integer-value of 1 and a
     * case-insensitive string 'true' to an LDAP-compatible 'TRUE'. All other
     * other values are converted to an LDAP-compatible 'FALSE'.
     *
     * @param  bool|int|string $value The boolean value to encode
     * @return string
     */
    public static function toLdapBoolean($value)
    {
        $return = 'FALSE';
        if (!is_scalar($value)) {
            return $return;
        }
        if (true === $value || 'true' === strtolower($value) || 1 === $value) {
            $return = 'TRUE';
        }
        return $return;
    }

    /**
     * Serialize any value for storage in LDAP
     *
     * @param mixed $value The value to serialize
     * @return string
     */
    public static function toLdapSerialize($value)
    {
        return serialize($value);
    }

    /**
     * Convert an LDAP-compatible value to a corresponding PHP-value.
     *
     * By setting the <var>$type</var>-parameter the conversion of a certain
     * type can be forced.
     *
     * @see Converter::STANDARD
     * @see Converter::BOOLEAN
     * @see Converter::GENERALIZED_TIME
     * @param string  $value         The value to convert
     * @param int     $type          The conversion type to use
     * @param  bool $dateTimeAsUtc Return DateTime values in UTC timezone
     * @return mixed
     */
    public static function fromLdap($value, $type = self::STANDARD, $dateTimeAsUtc = true)
    {
        switch ($type) {
            case self::BOOLEAN:
                return static::fromldapBoolean($value);
                break;
            case self::GENERALIZED_TIME:
                return static::fromLdapDateTime($value);
                break;
            default:
                if (is_numeric($value)) {
                    // prevent numeric values to be treated as date/time
                    return $value;
                } elseif ('TRUE' === $value || 'FALSE' === $value) {
                    return static::fromLdapBoolean($value);
                }
                if (preg_match('/^\d{4}[\d\+\-Z\.]*$/', $value)) {
                    return static::fromLdapDateTime($value, $dateTimeAsUtc);
                }
                try {
                    return static::fromLdapUnserialize($value);
                } catch (Exception\UnexpectedValueException $e) {
                    // Do nothing
                }
                break;
        }

        return $value;
    }

    /**
     * Convert an LDAP-Generalized-Time-entry into a DateTime-Object
     *
     * CAVEAT: The DateTime-Object returned will always be set to UTC-Timezone.
     *
     * @param string  $date  The generalized-Time
     * @param  bool $asUtc Return the DateTime with UTC timezone
     * @return DateTime
     * @throws Exception\InvalidArgumentException if a non-parseable-format is given
     */
    public static function fromLdapDateTime($date, $asUtc = true)
    {
        $datepart = array();
        if (!preg_match('/^(\d{4})/', $date, $datepart)) {
            throw new Exception\InvalidArgumentException('Invalid date format found');
        }

        if ($datepart[1] < 4) {
            throw new Exception\InvalidArgumentException('Invalid date format found (too short)');
        }

        $time = array(
            // The year is mandatory!
            'year'          => $datepart[1],
            'month'         => 1,
            'day'           => 1,
            'hour'          => 0,
            'minute'        => 0,
            'second'        => 0,
            'offdir'        => '+',
            'offsethours'   => 0,
            'offsetminutes' => 0
        );

        $length = strlen($date);

        // Check for month.
        if ($length >= 6) {
            $month = substr($date, 4, 2);
            if ($month < 1 || $month > 12) {
                throw new Exception\InvalidArgumentException('Invalid date format found (invalid month)');
            }
            $time['month'] = $month;
        }

        // Check for day
        if ($length >= 8) {
            $day = substr($date, 6, 2);
            if ($day < 1 || $day > 31) {
                throw new Exception\InvalidArgumentException('Invalid date format found (invalid day)');
            }
            $time['day'] = $day;
        }

        // Check for Hour
        if ($length >= 10) {
            $hour = substr($date, 8, 2);
            if ($hour < 0 || $hour > 23) {
                throw new Exception\InvalidArgumentException('Invalid date format found (invalid hour)');
            }
            $time['hour'] = $hour;
        }

        // Check for minute
        if ($length >= 12) {
            $minute = substr($date, 10, 2);
            if ($minute < 0 || $minute > 59) {
                throw new Exception\InvalidArgumentException('Invalid date format found (invalid minute)');
            }
            $time['minute'] = $minute;
        }

        // Check for seconds
        if ($length >= 14) {
            $second = substr($date, 12, 2);
            if ($second < 0 || $second > 59) {
                throw new Exception\InvalidArgumentException('Invalid date format found (invalid second)');
            }
            $time['second'] = $second;
        }

        // Set Offset
        $offsetRegEx = '/([Z\-\+])(\d{2}\'?){0,1}(\d{2}\'?){0,1}$/';
        $off         = array();
        if (preg_match($offsetRegEx, $date, $off)) {
            $offset = $off[1];
            if ($offset == '+' || $offset == '-') {
                $time['offdir'] = $offset;
                // we have an offset, so lets calculate it.
                if (isset($off[2])) {
                    $offsetHours = substr($off[2], 0, 2);
                    if ($offsetHours < 0 || $offsetHours > 12) {
                        throw new Exception\InvalidArgumentException('Invalid date format found (invalid offset hour)');
                    }
                    $time['offsethours'] = $offsetHours;
                }
                if (isset($off[3])) {
                    $offsetMinutes = substr($off[3], 0, 2);
                    if ($offsetMinutes < 0 || $offsetMinutes > 59) {
                        throw new Exception\InvalidArgumentException('Invalid date format found (invalid offset minute)');
                    }
                    $time['offsetminutes'] = $offsetMinutes;
                }
            }
        }

        // Raw-Data is present, so lets create a DateTime-Object from it.
        $offset     = $time['offdir']
                      . str_pad($time['offsethours'], 2, '0', STR_PAD_LEFT)
                      . str_pad($time['offsetminutes'], 2, '0', STR_PAD_LEFT);
        $timestring = $time['year'] . '-'
                      . str_pad($time['month'], 2, '0', STR_PAD_LEFT) . '-'
                      . str_pad($time['day'], 2, '0', STR_PAD_LEFT) . ' '
                      . str_pad($time['hour'], 2, '0', STR_PAD_LEFT) . ':'
                      . str_pad($time['minute'], 2, '0', STR_PAD_LEFT) . ':'
                      . str_pad($time['second'], 2, '0', STR_PAD_LEFT)
                      . $time['offdir']
                      . str_pad($time['offsethours'], 2, '0', STR_PAD_LEFT)
                      . str_pad($time['offsetminutes'], 2, '0', STR_PAD_LEFT);
        $date       = new DateTime($timestring);
        if ($asUtc) {
            $date->setTimezone(new DateTimeZone('UTC'));
        }
        return $date;
    }

    /**
     * Convert an LDAP-compatible boolean value into a PHP-compatible one
     *
     * @param string $value The value to convert
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    public static function fromLdapBoolean($value)
    {
        if ('TRUE' === $value) {
            return true;
        } elseif ('FALSE' === $value) {
            return false;
        } else {
            throw new Exception\InvalidArgumentException('The given value is not a boolean value');
        }
    }

    /**
     * Unserialize a serialized value to return the corresponding object
     *
     * @param string $value The value to convert
     * @return mixed
     * @throws Exception\UnexpectedValueException
     */
    public static function fromLdapUnserialize($value)
    {
        ErrorHandler::start(E_NOTICE);
        $v = unserialize($value);
        ErrorHandler::stop();

        if (false === $v && $value != 'b:0;') {
            throw new Exception\UnexpectedValueException('The given value could not be unserialized');
        }
        return $v;
    }
}
