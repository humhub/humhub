<?php

namespace humhub\helpers;

use yii\base\InvalidArgumentException;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;

class EnvHelper
{
    private const FIXED_SETTING_PREFIX = 'HUMHUB_SETTINGS.';
    private const PARAM_PREFIX = 'HUMHUB_PARAMS.';
    private const PARAMS_PATH = 'params';
    private const FIXED_SETTINGS_PATH = 'fixed-settings';

    public static function toConfig(?array $env = []): array
    {
        $config = [];

        foreach ($env as $key => $value) {
            try {
                $value = Json::decode($value);
            } catch (InvalidArgumentException) {
            }

            if (StringHelper::startsWith($key, self::FIXED_SETTING_PREFIX)) {
                ArrayHelper::setValue(
                    $config,
                    [
                        self::PARAMS_PATH, self::FIXED_SETTINGS_PATH,
                        ...self::keyToPath(str_replace(self::FIXED_SETTING_PREFIX, '', $key)),
                    ],
                    $value,
                );
            }
            if (StringHelper::startsWith($key, self::PARAM_PREFIX)) {
                ArrayHelper::setValue(
                    $config,
                    [
                        self::PARAMS_PATH,
                        ...self::keyToPath(str_replace(self::PARAM_PREFIX, '', $key)),
                    ],
                    $value,
                );
            }
        }

        return $config;
    }

    private static function keyToPath(string $key): array
    {
        return ArrayHelper::getColumn(
            explode('.', $key),
            function ($path) {
                return Inflector::variablize(strtolower($path));
            },
        );
    }
}
