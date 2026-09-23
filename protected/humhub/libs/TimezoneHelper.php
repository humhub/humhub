<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use DateTime;
use DateTimeZone;
use Yii;
use yii\db\Exception;

/**
 * TimezoneHelpers
 *
 * @author luke
 */
class TimezoneHelper
{
    /**
     * Time zone identifiers which have been renamed or merged in the tz database, mapped to their current name.
     *
     * The legacy names only exist as "backward" compatibility links in the tz database and are missing on systems
     * which ship without those links (e.g. Debian 13 or Ubuntu 24.04 without the `tzdata-legacy` package).
     *
     * @since 1.18.7
     */
    public const LEGACY_IDENTIFIERS = [
        'Africa/Asmera' => 'Africa/Asmara',
        'America/Godthab' => 'America/Nuuk',
        'America/Nipigon' => 'America/Toronto',
        'America/Pangnirtung' => 'America/Iqaluit',
        'America/Rainy_River' => 'America/Winnipeg',
        'America/Thunder_Bay' => 'America/Toronto',
        'Asia/Ashkhabad' => 'Asia/Ashgabat',
        'Asia/Calcutta' => 'Asia/Kolkata',
        'Asia/Choibalsan' => 'Asia/Ulaanbaatar',
        'Asia/Dacca' => 'Asia/Dhaka',
        'Asia/Katmandu' => 'Asia/Kathmandu',
        'Asia/Macao' => 'Asia/Macau',
        'Asia/Rangoon' => 'Asia/Yangon',
        'Asia/Saigon' => 'Asia/Ho_Chi_Minh',
        'Asia/Thimbu' => 'Asia/Thimphu',
        'Asia/Ulan_Bator' => 'Asia/Ulaanbaatar',
        'Atlantic/Faeroe' => 'Atlantic/Faroe',
        'Europe/Kiev' => 'Europe/Kyiv',
        'Europe/Uzhgorod' => 'Europe/Kyiv',
        'Europe/Zaporozhye' => 'Europe/Kyiv',
        'Pacific/Enderbury' => 'Pacific/Kanton',
    ];

    /**
     * Checks whether the given identifier is known to the tz database of this PHP installation and can therefore
     * be used with `date_default_timezone_set()`.
     *
     * @since 1.18.7
     */
    public static function isValid(?string $timeZone): bool
    {
        static $identifiers = null;
        if ($identifiers === null) {
            $identifiers = array_fill_keys(DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC), true);
        }

        return $timeZone !== null && isset($identifiers[$timeZone]);
    }

    /**
     * Returns a replacement for a time zone identifier which is unknown to this PHP installation: the current name
     * of a renamed zone (e.g. `Europe/Kyiv` for `Europe/Kiev`) if available, otherwise the given fallback.
     * The problem is logged as an error.
     *
     * @since 1.18.7
     */
    public static function replaceUnknown(string $timeZone, string $fallback): string
    {
        $replacement = self::LEGACY_IDENTIFIERS[$timeZone] ?? null;
        if ($replacement === null || !self::isValid($replacement)) {
            $replacement = $fallback;
        }

        Yii::error(sprintf(
            'The time zone "%s" is not supported by this PHP installation, "%s" is used instead. '
            . 'Install the legacy time zone data of your operating system (e.g. the package "tzdata-legacy") if the original time zone is required.',
            $timeZone,
            $replacement,
        ));

        return $replacement;
    }

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
            $timezone_offsets[$timezone] = $tz->getOffset(new DateTime());
        }

        // sort timezone by timezone name
        asort($timezone_offsets);

        $timezone_list = [];

        foreach ($timezone_offsets as $timezone => $offset) {
            if ($withOffset) {
                $offset_prefix = $offset < 0 ? '-' : '+';
                $offset_formatted = gmdate('H:i', abs($offset));
                $pretty_offset = 'UTC' . $offset_prefix . $offset_formatted;
                $timezone_list[$timezone] = $pretty_offset . ' - ' . $timezone;
            } else {
                $timezone_list[$timezone] = $timezone;
            }
        }

        return $timezone_list;
    }

    /**
     * Returns the date time from the database connection
     *
     * @return DateTime
     * @deprecated since 1.17 because it is not used anymore
     */
    public static function getDatabaseConnectionTime(): DateTime
    {
        $timestamp = Yii::$app->db->createCommand('SELECT NOW()')->queryScalar();
        return DateTime::createFromFormat("Y-m-d H:i:s", $timestamp);
    }

    /**
     * Get a time value from time zone title
     *
     * @param string $timeZone
     * @return string
     * @since v1.17
     */
    public static function convertToTime(string $timeZone): string
    {
        try {
            $offset = (new DateTimeZone($timeZone))->getOffset(new DateTime());
            $offset_prefix = $offset < 0 ? '-' : '+';
            return $offset_prefix . gmdate('G:i', abs($offset));
        } catch (\Exception $e) {
            Yii::error('Wrong time zone: ' . $e->getMessage());
            return '+0:00';
        }
    }
}
