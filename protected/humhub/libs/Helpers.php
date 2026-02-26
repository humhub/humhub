<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\exceptions\InvalidArgumentValueException;
use Yii;

/**
 * This class contains a lot of html helpers for the views
 *
 * @since 0.5
 */
class Helpers
{
    /**
     * Shorten a text string
     *
     * @param string $text - Text string you will shorten
     * @param int $length - Count of characters to show
     *
     * @return string
     */
    public static function truncateText($text, $length): string
    {
        return self::trimText($text, $length);
    }

    public static function trimText($text, $length): string
    {
        $text = trim((string) preg_replace('#<br */?>#i', ' ', (string) $text));

        $length = abs((int)$length);
        if (mb_strlen($text) > $length) {
            $text = trim(mb_substr($text, 0, $length)) . '...';
        }

        return $text;
    }

    /**
     * Compare two arrays values
     *
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
     * Get a readable time format from seconds
     *
     * @param string $sekunden - Seconds you will formatting
     * */
    public static function getFormattedTime($sekunden)
    {
        $minus = '';
        if ($sekunden < 0) {
            $sekunden *= -1;
            $minus = '-';
        }

        $minuten = bcdiv((string) $sekunden, '60', 0);

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

        return match (substr($valueString, -1)) {
            'M', 'm' => (int)$valueString * 1048576,
            'K', 'k' => (int)$valueString * 1024,
            'G', 'g' => (int)$valueString * 1073741824,
            default => (int)$valueString,
        };
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
     *
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
     *
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
