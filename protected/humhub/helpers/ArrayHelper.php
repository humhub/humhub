<?php

namespace humhub\helpers;

class ArrayHelper extends \yii\helpers\ArrayHelper
{
    public static function flatten($array, $separator = '.', string $path = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge(
                    $result,
                    static::flatten($value, $separator, (empty($path)) ? $key : $path . $separator . $key),
                );
            } else {
                $result = array_merge($result, [$path . $separator . $key => $value]);
            }

        }
        return $result;
    }
}
