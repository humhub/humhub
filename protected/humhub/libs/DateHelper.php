<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

/**
 * Utility class for date issues
 *
 * @see \yii\validators\DateValidator
 * @author buddha
 */
class DateHelper
{
    /**
     * Parses a date and optionally a time if timeAttribute is specified.
     * 
     * @param string $value
     * @param string $timeValue optional time value
     * @return int timestamp in utc
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
     * @param string $pattern  pattern
     * @param string $timeValue optional time value
     * @return int timestamp in utc
     */
    public static function parseDateTime($value, $pattern = 'Y-m-d', $timeValue = null)
    {
        $ts = self::parseDateTimeToTimestamp($value, $timeValue);
        $dt = new \DateTime();
        $dt->setTimestamp($ts);
        return $dt->format($pattern);
    }
}
