<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\libs;

class Sort
{
    public static function sort(&$arr, $field = 'sortOrder', $default = PHP_INT_MAX)
    {
        usort($arr, function ($a, $b) use ($field, $default) {
            $sortA = static::getSortValue($a, $field, $default);
            $sortB = static::getSortValue($b, $field, $default);

            if ($sortA == $sortB) {
                return 0;
            } elseif ($sortA < $sortB) {
                return -1;
            } else {
                return 1;
            }
        });

        return $arr;
    }

    private static function getSortValue($obj, $field, $default)
    {
        if (is_array($obj) && isset($obj[$field])) {
            return $obj[$field] === null ? $default : $obj[$field];
        }

        if (property_exists($obj, $field)) {
            return $obj->$field === null ? $default : $obj->$field;
        }

        return PHP_INT_MAX;
    }

}
