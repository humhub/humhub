<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use DateTime;
use DateTimeZone;


/**
 * TimezoneHelpers
 *
 * @author luke
 */
class TimezoneHelper
{

    /**
     * 
     * // Modified version of the timezone list function from http://stackoverflow.com/a/17355238/507629
     * // Includes current time for each timezone (would help users who don't know what their timezone is)
     * 
     * @staticvar array $regions
     * @return type
     */
    public static function generateList()
    {
        static $regions = array(
            DateTimeZone::AFRICA,
            DateTimeZone::AMERICA,
            DateTimeZone::ANTARCTICA,
            DateTimeZone::ASIA,
            DateTimeZone::ATLANTIC,
            DateTimeZone::AUSTRALIA,
            DateTimeZone::EUROPE,
            DateTimeZone::INDIAN,
            DateTimeZone::PACIFIC,
        );

        $timezones = array();
        foreach ($regions as $region) {
            $timezones = array_merge($timezones, DateTimeZone::listIdentifiers($region));
        }

        $timezone_offsets = array();
        foreach ($timezones as $timezone) {
            $tz = new DateTimeZone($timezone);
            $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
        }

        // sort timezone by timezone name
        #ksort($timezone_offsets);
        asort($timezone_offsets);

        $timezone_list = array();

        foreach ($timezone_offsets as $timezone => $offset) {
            $offset_prefix = $offset < 0 ? '-' : '+';
            $offset_formatted = gmdate('H:i', abs($offset));

            $pretty_offset = "UTC${offset_prefix}${offset_formatted}";

            $t = new DateTimeZone($timezone);
            $c = new DateTime(null, $t);
            #$current_time = Yii::$app->formatter->asTime($c, 'short'); #;
            $current_time = $c->format('H:i');

#            $timezone_list[$timezone] = $pretty_offset." - ".$current_time."  - ".$timezone;
            $timezone_list[$timezone] = $pretty_offset . " - " . $timezone;
        }

        return $timezone_list;
    }

}
