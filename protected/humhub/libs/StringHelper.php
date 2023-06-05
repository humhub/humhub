<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Stringable;

/**
 * StringHelper
 *
 * @since 1.1
 * @author luke
 */
class StringHelper extends \yii\helpers\StringHelper
{
    /**
     * Converts (LDAP) Binary to Ascii GUID
     *
     * @param string $object_guid a binary string containing data.
     *
     * @return string the guid
     */
    public static function binaryToGuid($object_guid)
    {
        $hex_guid = bin2hex($object_guid);

        if ($hex_guid == '') {
            return '';
        }

        $hex_guid_to_guid_str = '';
        for ($k = 1; $k <= 4; ++$k) {
            $hex_guid_to_guid_str .= substr($hex_guid, 8 - 2 * $k, 2);
        }
        $hex_guid_to_guid_str .= '-';
        for ($k = 1; $k <= 2; ++$k) {
            $hex_guid_to_guid_str .= substr($hex_guid, 12 - 2 * $k, 2);
        }
        $hex_guid_to_guid_str .= '-';
        for ($k = 1; $k <= 2; ++$k) {
            $hex_guid_to_guid_str .= substr($hex_guid, 16 - 2 * $k, 2);
        }
        $hex_guid_to_guid_str .= '-' . substr($hex_guid, 16, 4);
        $hex_guid_to_guid_str .= '-' . substr($hex_guid, 20);

        return strtolower($hex_guid_to_guid_str);
    }


    /**
     * @param mixed $string String to test, and if $convert is true, to turn into string
     * @param bool $convert
     *
     * @return bool
     * @since 1.15
     */
    public static function isStringable(&$string, bool $convert = true): bool
    {
        switch (getType($string)) {
            case "string":
                return true;

            case "bool":
            case "boolean":
                if (!$convert) {
                    return true;
                }
                $string = (string)(int)$string;
                return true;

            case "int":
            case "integer":
            case "null":
                if (!$convert) {
                    return true;
                }
                $string = (string)$string;
                return true;

            case "double":
            case "float":
                if (!$convert) {
                    return true;
                }
                $string = \yii\helpers\StringHelper::floatToString($string);
                return true;

            case "array":
                return false;

            case "object":
                if ($string instanceof Stringable || (is_object($string) && is_callable([$string, '__toString']))) {
                    if (!$convert) {
                        return true;
                    }
                    $string = (string)$string;
                    return true;
                }
                return false;

            default:
                // "resource", "NULL", "unknown type", "resource (closed)"
                return false;
        }
    }
}
