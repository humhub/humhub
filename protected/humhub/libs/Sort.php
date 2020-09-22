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
    public static function sort(&$arr, $field = 'sortOrder')
    {
        usort($arr, function($a, $b) use ($field) {
            $sortA = static::getSortValue($a, $field);
            $sortB = static::getSortValue($b, $field);

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

    private static function getSortValue($obj, $field)
    {
        if(is_array($obj) && isset($obj[$field])) {
            return $obj[$field] === null ? PHP_INT_MAX : $obj[$field];
        }

        if(property_exists($obj, $field)) {
            return $obj->$field === null ? PHP_INT_MAX : $obj->$field;
        }

        return PHP_INT_MAX;
    }

}
