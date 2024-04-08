<?php

namespace humhub\helpers;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use function humhub\libs\array_flatten;

class ArrayHelper extends \yii\helpers\ArrayHelper
{

    public static function flatten(array $array, string $seperator = '.', string $path = '')
    {
        if (!is_array($array)) {
            return false;
        }

        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, static::flatten($value, $seperator, (empty($path)) ? $key : $path . $seperator . $key));
            } else {
                $result = array_merge($result, array($path . $seperator . $key => $value));
            }

        }
        return $result;
    }

}
