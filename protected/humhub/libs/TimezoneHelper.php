<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
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
     * @param bool $includeUTC whether or not to include UTC timeZone
     * @param bool $withOffset whether or not to add offset information
     * @return array
     * @throws \Exception
     */
    public static function generateList($includeUTC = false, $withOffset = true)
    {
        $regions = [
            DateTimeZone::AFRICA,
            DateTimeZone::AMERICA,
            DateTimeZone::ANTARCTICA,
            DateTimeZone::ASIA,
            DateTimeZone::ATLANTIC,
            DateTimeZone::AUSTRALIA,
            DateTimeZone::EUROPE,
            DateTimeZone::INDIAN,
            DateTimeZone::PACIFIC,
        ];

        if ($includeUTC) {
            $regions[] = DateTimeZone::UTC;
        }

        $timezones = [];
        foreach ($regions as $region) {
            $timezones = array_merge($timezones, DateTimeZone::listIdentifiers($region));
        }

        $timezone_offsets = [];
        foreach ($timezones as $timezone) {
            $tz = new DateTimeZone($timezone);
            $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
        }

        // sort timezone by timezone name
        asort($timezone_offsets);

        $timezone_list = [];

        foreach ($timezone_offsets as $timezone => $offset) {
            if($withOffset) {
                $offset_prefix = $offset < 0 ? '-' : '+';
                $offset_formatted = gmdate('H:i', abs($offset));
                $pretty_offset = "UTC${offset_prefix}${offset_formatted}";
                $timezone_list[$timezone] = $pretty_offset . ' - ' . $timezone;
            } else {
                $timezone_list[$timezone] = $timezone;
            }
        }

        return $timezone_list;
    }
}
