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
     * @param string|null $type returns the input type. If the input is an object, it returns its class. For diagnostic
     *        output you can use `echo static::toString($input, $type) ?? $type;`
     *
     * @return string|null
     * @since 1.15
     */
    public static function toString(&$string, ?string &$type = null): ?string
    {
        $type = getType($string);

        switch ($type) {
            case "string":
                return $string;

            case "bool":
            case "boolean":
                return (string)(int)$string;

            case "int":
            case "integer":
            case "null":
                return (string)$string;

            case "double":
            case "float":
                return \yii\helpers\StringHelper::floatToString($string);

            case "object":
                $type = get_class($string);

                if ($string instanceof Stringable || (is_object($string) && is_callable([$string, '__toString']))) {
                    return (string)$string;
                }

                return null;

            default:
                // "array", "resource", "unknown type", "resource (closed)"
                return null;
        }
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
        $result = static::toString($string, $type);

        if ($result === null) {
            return false;
        }

        if ($convert) {
            $string = $result;
        }

        return true;
    }
}
