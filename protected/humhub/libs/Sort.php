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
            $sortA = (isset($a[$field])) ? $a[$field] : PHP_INT_MAX;
            $sortB = (isset($b[$field])) ? $b[$field] : PHP_INT_MAX;

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
}
