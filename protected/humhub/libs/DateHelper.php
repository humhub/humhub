<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use DateTimeZone;
use Yii;

/**
 * Utility class for date issues
 *
 * @see \yii\validators\DateValidator
 * @author buddha
 */
class DateHelper
{
    const DB_DATE_FORMAT = 'Y-m-d H:i:s';
    const DB_DATE_FORMAT_PHP = 'php:Y-m-d H:i:s';
    const REGEX_DBFORMAT_DATE = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';
    const REGEX_DBFORMAT_DATETIME = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) (\d{2}):(\d{2}):(\d{2})$/';

    /**
     * Returns the user timeZone or app timezone as fallback.
     *
     * @return DateTimeZone|string
     * @since v1.4
     */
    public static function getUserTimeZone($asString = false)
    {
        $tz =  Yii::$app->user->isGuest
            ? Yii::$app->timeZone
            : Yii::$app->user->getTimeZone();

        if(!$tz) {
            $tz = Yii::$app->timeZone;
        }

        return  $asString ? $tz : new DateTimeZone($tz);
    }

    /**
     * @param bool $asString
     * @return DateTimeZone|string
     * @since v1.4
     */
    public static function getSystemTimeZone($asString = false)
    {
        return $asString ? Yii::$app->timeZone : new DateTimeZone(Yii::$app->timeZone);
    }

    /**
     * Checks whether the given value is a db date format or not.
     *
     * If $dateOnly flag is set to true this method tests against date format without time, otherwise it will test against
     * the db datetime format.
     *
     * @param string $value the date value
     * @param bool $dateOnly
     * @return boolean
     * @since v1.4
     */
    public static function isInDbFormat($value, $dateOnly = false)
    {
        return (boolean) ($dateOnly ? preg_match(self::REGEX_DBFORMAT_DATE, $value) : preg_match(self::REGEX_DBFORMAT_DATETIME, $value));
    }

    /**
     * Parses a date and optionally a time if timeAttribute is specified.
     *
     * @param string $value
     * @param string $timeValue optional time value
     * @return int|false timestamp in utc
     * @throws \Exception
     */
    public static function parseDateTimeToTimestamp($value, $timeValue = null)
    {
        return DbDateValidator::parseDateTime($value, $timeValue);
    }

    /**
     * Parses a date and optionally a time if timeAttribute is specified to
     * an given pattern or the default pattern 'Y-m-d' if no pattern is provided.
     *
     * @param string $value date value
     * @param string $pattern pattern
     * @param string $timeValue optional time value
     * @return int|false timestamp in utc or false in case value was could not be parsed
     * @throws \Exception
     */
    public static function parseDateTime($value, $pattern = 'Y-m-d', $timeValue = null)
    {
        $ts = self::parseDateTimeToTimestamp($value, $timeValue);

        if($ts === false) {
            return false;
        }

        $dt = new \DateTime();
        $dt->setTimestamp($ts);

        return $dt->format($pattern);
    }
}
