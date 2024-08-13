<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\exceptions\InvalidArgumentClassException;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\exceptions\InvalidArgumentValueException;
use Yii;
use yii\base\InvalidArgumentException;

/**
 * This class contains a lot of html helpers for the views
 *
 * @since 0.5
 */
class Helpers
{
    public const CLASS_CHECK_INVALID_CLASSNAME_PARAMETER = 1;
    public const CLASS_CHECK_INVALID_TYPE_PARAMETER = 2;
    public const CLASS_CHECK_VALUE_IS_EMPTY = 4;
    public const CLASS_CHECK_INVALID_TYPE = 8;
    public const CLASS_CHECK_NON_EXISTING_CLASS = 16;
    public const CLASS_CHECK_TYPE_NOT_IN_LIST = 32;
    public const CLASS_CHECK_VALUE_IS_INSTANCE = 64;
    public const CLASS_CHECK_VALUE_IS_NULL = 128;

    /**
     * Shorten a text string
     *
     * @param string $text - Text string you will shorten
     * @param integer $length - Count of characters to show
     * @return string
     */
    public static function truncateText($text, $length): string
    {
        return self::trimText($text, $length);
    }

    public static function trimText($text, $length): string
    {
        $text = trim(preg_replace('#<br */?>#i', ' ', $text));

        $length = abs((int)$length);
        if (mb_strlen($text) > $length) {
            $text = trim(mb_substr($text, 0, $length)) . '...';
        }

        return $text;
    }

    /**
     * Compare two arrays values
     * @param array $a - First array to compare against..
     * @param array $b - Second array
     *
     * convert Objects: Helpers::arrayCompVal((array)$obj1, (array)$obj2)
     *
     * */

    public static function arrayCompVal($a, $b)
    {
        if (!is_array($a) || !is_array($b)) {
            return false;
        }
        sort($a);
        sort($b);

        return $a == $b;
    }

    /**
     * Temp Function to use UTF8 SubStr
     * @deprecated since 1.11 Use mb_substr() instead.
     *
     * @param string $str
     * @param integer $from
     * @param integer $len
     * @return string
     */
    public static function substru($str, $from, $len): string
    {
        return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $from . '}' . '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $len . '}).*#s', '$1', $str);
    }

    /**
     * Get a readable time format from seconds
     * @param string $sekunden - Seconds you will formatting
     * */
    public static function getFormattedTime($sekunden)
    {
        $minus = '';
        if ($sekunden < 0) {
            $sekunden *= -1;
            $minus = '-';
        }

        $minuten = bcdiv($sekunden, '60', 0);

        $stunden = bcdiv($minuten, '60', 0);
        $minuten = bcmod($minuten, '60');

        if ($minuten < 10) {
            $minuten = '0' . $minuten;
        }

        $stunden = bcmod($stunden, '24');

        return $minus . $stunden . ':' . $minuten;
    }

    /**
     * Returns bytes of a PHP Ini Setting Value
     * E.g. 10M will converted into 10485760
     *
     * Source: http://php.net/manual/en/function.ini-get.php
     *
     * @param String $val
     * @return int bytes
     * @deprecated bug on PHP7 "A non well formed numeric value encountered"
     * @see \humhub\libs\Helpers::getBytesOfIniValue instead
     */
    public static function GetBytesOfPHPIniValue($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    /**
     * Returns bytes of a PHP Ini Setting Value
     * E.g. 10M will converted into 10485760
     *
     * Source: http://php.net/manual/en/function.ini-get.php#96996
     *
     * @param string $valueString
     *
     * @return int bytes
     * @throws InvalidArgumentValueException
     */
    public static function getBytesOfIniValue($valueString)
    {
        if ($valueString === null || $valueString === '') {
            return 0;
        }

        if ($valueString === false) {
            throw new InvalidArgumentValueException('Your configuration option of ini_get function does not exist.');
        }

        switch (substr($valueString, -1)) {
            case 'M':
            case 'm':
                return (int)$valueString * 1048576;
            case 'K':
            case 'k':
                return (int)$valueString * 1024;
            case 'G':
            case 'g':
                return (int)$valueString * 1073741824;
            default:
                return (int)$valueString;
        }
    }

    /**
     * Returns a unique string
     *
     * @return string unique
     */
    public static function GetUniqeId()
    {
        return str_replace('.', '', uniqid('', true));
    }

    /**
     * Checks if the class has this class as one of its parents
     *
     * Code of the thrown Exception is a bit-mask consisting of the following bits
     * - self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER: Invalid $className parameter
     * - self::CLASS_CHECK_INVALID_TYPE_PARAMETER: Invalid $type parameter
     * - self::CLASS_CHECK_VALUE_IS_EMPTY: Empty parameter
     * - self::CLASS_CHECK_INVALID_TYPE: Invalid type
     * - self::CLASS_CHECK_NON_EXISTING_CLASS: Non-existing class
     * - self::CLASS_CHECK_TYPE_NOT_IN_LIST: Class that is not in $type parameter
     * - self::CLASS_CHECK_VALUE_IS_INSTANCE: $className is an object instance
     * - self::CLASS_CHECK_VALUE_IS_NULL: NULL value
     *
     * @param string|object|null|mixed $className Object or classname to be checked. Null may be valid if included in $type.
     *        Everything else is invalid and either throws an error (default) or returns NULL, if $throw is false.
     * @param string|string[] $types (List of) class, interface or trait names that are allowed.
     *        If NULL is included, NULL values are also allowed.
     * @param bool $throw Determines if an Exception should be thrown if $className doesn't match $type, or simply return NULL.
     *        Invalid $types always throw an error!
     * @param bool $strict If set to true, no invalid characters are removed from a $className string.
     *        If set to false, please make sure you use the function's return value, rather than $className, as they might diverge
     *
     * @return string|null
     * @throws InvalidArgumentTypeException|InvalidArgumentClassException|InvalidArgumentValueException
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public static function checkClassType($className, $types, bool $throw = true, ?bool $strict = true): ?string
    {
        if (empty($types)) {
            throw new InvalidArgumentValueException('$type', ['string', 'string[]'], $types, self::CLASS_CHECK_INVALID_TYPE_PARAMETER + self::CLASS_CHECK_VALUE_IS_EMPTY);
        }

        $types = (array)$types;
        $valid = [];
        $allowNull = false;

        // validate the type array
        foreach ($types as $index => &$item) {
            if ($item === null) {
                $allowNull = true;
                continue;
            }

            if (is_object($item)) {
                $valid[get_class($item)] = false;
                continue;
            }

            if (!is_string($item)) {
                throw new InvalidArgumentValueException(sprintf('$type[%s]', $index), ['class', 'object'], $item, self::CLASS_CHECK_INVALID_TYPE_PARAMETER + self::CLASS_CHECK_INVALID_TYPE);
            }

            $isTrait = false;

            if (!class_exists($item) && !interface_exists($item, false) && !($isTrait = trait_exists($item, false))) {
                throw new InvalidArgumentValueException(sprintf('$type[%s]', $index), 'a valid class/interface/trait name or an object instance', $item, self::CLASS_CHECK_INVALID_TYPE_PARAMETER + self::CLASS_CHECK_NON_EXISTING_CLASS);
            }

            $valid[$item] = $isTrait;
        }
        // make sure the reference is not going to be overwritten
        unset($item);

        // save the types for throwing exceptions
        $types = array_keys($valid);
        if ($allowNull) {
            $types[] = null;
        }

        // check for null input
        if ($className === null) {
            // check if null is allowed
            if ($allowNull) {
                return null;
            }

            if (!$throw) {
                return null;
            }

            throw new InvalidArgumentTypeException(
                '$className',
                $types,
                $className,
                self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + self::CLASS_CHECK_VALUE_IS_EMPTY + self::CLASS_CHECK_INVALID_TYPE + self::CLASS_CHECK_VALUE_IS_NULL
            );
        }

        // check for other empty input
        if (empty($className)) {
            if ((!$strict && $allowNull) || !$throw) {
                return null;
            }

            throw is_string($className)
                    ? new InvalidArgumentClassException(
                        '$className',
                        $types,
                        $className,
                        self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + self::CLASS_CHECK_VALUE_IS_EMPTY + self::CLASS_CHECK_INVALID_TYPE + self::CLASS_CHECK_TYPE_NOT_IN_LIST
                    )
                    : new InvalidArgumentTypeException(
                        '$className',
                        $types,
                        $className,
                        self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + self::CLASS_CHECK_VALUE_IS_EMPTY + self::CLASS_CHECK_INVALID_TYPE
                    )
            ;
        }

        // Validation for object instances
        if (is_object($className)) {
            foreach ($valid as $matchingClass => $isTrait) {
                if ($isTrait) {
                    if (in_array($matchingClass, static::classUsesTraits($className, false), true)) {
                        return get_class($className);
                    }
                } elseif ($className instanceof $matchingClass) {
                    return get_class($className);
                }
            }

            if (!$throw) {
                return null;
            }

            throw new InvalidArgumentClassException(
                '$className',
                $types,
                $className,
                self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + self::CLASS_CHECK_TYPE_NOT_IN_LIST + self::CLASS_CHECK_VALUE_IS_INSTANCE
            );
        }

        if (!is_string($className)) {
            if (!$throw) {
                return null;
            }

            throw new InvalidArgumentTypeException(
                '$className',
                $types,
                $className,
                self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + self::CLASS_CHECK_INVALID_TYPE
            );
        }

        $cleaned = preg_replace('/[^a-z0-9_\-\\\]/i', '', $className);

        if ($strict && $cleaned !== $className) {
            if (!$throw) {
                return null;
            }

            throw new InvalidArgumentClassException(
                '$className',
                'a valid class name or an object instance',
                $className,
                self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER
            );
        }

        $className = $cleaned;

        if (!class_exists($className)) {
            if (!$throw) {
                return null;
            }

            throw new InvalidArgumentValueException(
                '$className',
                'a valid class name or an object instance',
                $className,
                self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + self::CLASS_CHECK_NON_EXISTING_CLASS
            );
        }

        foreach ($valid as $matchingClass => $isTrait) {
            if ($isTrait) {
                if (in_array($matchingClass, static::classUsesTraits($className, false), true)) {
                    return $className;
                }
            } elseif (is_a($className, $matchingClass, true)) {
                return $className;
            }
        }

        if (!$throw) {
            return null;
        }

        throw new InvalidArgumentClassException(
            '$className',
            $types,
            $className,
            self::CLASS_CHECK_INVALID_CLASSNAME_PARAMETER + self::CLASS_CHECK_TYPE_NOT_IN_LIST
        );
    }

    /**
     * @param string|object $class
     * @param bool $autoload
     *
     * @return array|null
     * @see https://www.php.net/manual/en/function.class-uses.php#122427
     */
    public static function &classUsesTraits($class, bool $autoload = true): ?array
    {
        $traits = [];

        // Get all the traits of $class and its parent classes
        do {
            $class_name = is_object($class) ? get_class($class) : $class;

            if (class_exists($class_name, $autoload)) {
                $traits = array_merge(class_uses($class, $autoload), $traits);
            }
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traits_to_search = $traits;
        while (!empty($traits_to_search)) {
            $new_traits = class_uses(array_pop($traits_to_search), $autoload);
            $traits = array_merge($new_traits, $traits);
            $traits_to_search = array_merge($new_traits, $traits_to_search);
        };

        if (count($traits) === 0) {
            $traits = null;
        } else {
            $traits = array_unique($traits);
        }

        return $traits;
    }

    /**
     * Check for sameness of two strings using an algorithm with timing
     * independent of the string values if the subject strings are of equal length.
     *
     * The function can be useful to prevent timing attacks. For example, if $a and $b
     * are both hash values from the same algorithm, then the timing of this function
     * does not reveal whether or not there is a match.
     *
     * NOTE: timing is affected if $a and $b are different lengths or either is not a
     * string. For the purpose of checking password hash this does not reveal information
     * useful to an attacker.
     *
     * @see http://blog.astrumfutura.com/2010/10/nanosecond-scale-remote-timing-attacks-on-php-applications-time-to-take-them-seriously/
     * @see http://codereview.stackexchange.com/questions/13512
     * @see https://github.com/ircmaxell/password_compat/blob/master/lib/password.php
     *
     * @param string $a First subject string to compare.
     * @param string $b Second subject string to compare.
     * @return bool true if the strings are the same, false if they are different or if
     * either is not a string.
     */
    public static function same($a, $b)
    {
        if (!is_string($a) || !is_string($b)) {
            return false;
        }
        $mb = function_exists('mb_strlen');
        $length = $mb ? mb_strlen($a, '8bit') : strlen($a);
        if ($length !== ($mb ? mb_strlen($b, '8bit') : strlen($b))) {
            return false;
        }
        $check = 0;
        for ($i = 0; $i < $length; $i += 1) {
            $check |= (ord($a[$i]) ^ ord($b[$i]));
        }

        return $check === 0;
    }

    /**
     * Set sql_mode=TRADITIONAL for mysql server.
     *
     * This static function is intended as closure for on afterOpen raised by yii\db\Connection and
     * should be configured in dynamic.php like this: 'on afterOpen' => ['humhub\libs\Helpers', 'SqlMode'],
     *
     * This is mainly required for grouped notifications.
     *
     * @param $event
     * @since 1.2.1
     */
    public static function SqlMode($event)
    {
        /* set sql_mode only for mysql */
        if ($event->sender->driverName == 'mysql') {
            try {
                $event->sender->createCommand('SET SESSION sql_mode=""; SET SESSION sql_mode="NO_ENGINE_SUBSTITUTION"')->execute();
            } catch (\Exception $ex) {
                Yii::error('Could not switch SQL mode: ' . $ex->getMessage());
            }
        }
    }
}
