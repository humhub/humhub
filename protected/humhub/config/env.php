<?php


use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;

$env = [];

$preparePath = function(string $key): array {
    return ArrayHelper::getColumn(
        explode('__', $key),
        function($path) {
            return Inflector::variablize(strtolower($path));
        }
    );
};

foreach ($_ENV as $key => $value) {
    try {
        $value = Json::decode($value);
    } catch (InvalidArgumentException) {}

    if (StringHelper::startsWith($key, 'HUMHUB_SETTINGS_')) {
        ArrayHelper::setValue(
            $env,  [
                'params', 'fixed-settings',
                ...$preparePath(str_replace('HUMHUB_SETTINGS_', '', $key))
            ], $value
        );
    }
    if (StringHelper::startsWith($key, 'HUMHUB_PARAMS_')) {
        ArrayHelper::setValue(
            $env,  [
                'params',
                ...$preparePath(str_replace('HUMHUB_PARAMS_', '', $key))
            ], $value
        );
    }
}

return $env;
