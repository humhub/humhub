<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use DateTime;
use Yii;

/**
 * DateTimeHelper
 *
 * @author Luke
 */
class DateTimeHelper
{

    /**
     * Rounds given DateTime object to the next full hour
     * 
     * @param DateTime $dateTime
     * @return DateTime
     */
    public static function roundToNextFullHour(DateTime $dateTime = null)
    {
        if ($dateTime === null) {
            $dateTime = new DateTime();
        }

        $minutes = $dateTime->format('i');

        $dateTime->modify("+1 hour");

        if ($minutes > 0) {
            $dateTime->modify('-' . $minutes . ' minutes');
        }

        return $dateTime;
    }

    public static function getTimeFormat()
    {
        return Yii::$app->formatter->isShowMeridiem() ? 'h:mm a' : 'php:H:i';
    }

    public static function getDateInterval($startDateTime = null, $endDateTime)
    {
        if ($startDateTime === null) {
            $startDateTime = new DateTime;
        }

        if (is_string($startDateTime)) {
            $startDateTime = new DateTime($startDateTime);
        }

        if (is_string($endDateTime)) {
            $endDateTime = new DateTime($endDateTime);
        }

        return $startDateTime->diff($endDateTime);
    }

}
