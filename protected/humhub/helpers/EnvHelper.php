<?php

namespace humhub\helpers;

use yii\base\InvalidArgumentException;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;

class EnvHelper
{
    private const string FIXED_SETTING_PREFIX = 'HUMHUB_FIXED_SETTINGS';
    private const string MAIN_PREFIX = 'HUMHUB_CONFIG';
    private const array FIXED_SETTINGS_PATH = ['params', 'fixed-settings'];
    private const string DEPTH_SEPARATOR = '__';

    public static function toConfig(?array $env = []): array
    {
        $config = [];

        foreach ($env as $key => $value) {
            $value = self::normalizeValue($value);

            // Skip null values
            if (is_null($value)) {
                continue;
            }

            if (StringHelper::startsWith($key, self::FIXED_SETTING_PREFIX . self::DEPTH_SEPARATOR)) {
                ArrayHelper::setValue(
                    $config,
                    [
                        ...self::FIXED_SETTINGS_PATH,
                        ...self::keyToPath(str_replace(self::FIXED_SETTING_PREFIX . self::DEPTH_SEPARATOR, '', $key)),
                    ],
                    $value,
                );
            }
            if (StringHelper::startsWith($key, self::MAIN_PREFIX . self::DEPTH_SEPARATOR)) {
                ArrayHelper::setValue(
                    $config,
                    [
                        ...self::keyToPath(str_replace(self::MAIN_PREFIX . self::DEPTH_SEPARATOR, '', $key)),
                    ],
                    $value,
                );
            }
        }

        return $config;
    }

    private static function normalizeValue(mixed $value): mixed
    {
        try {
            // Try to decode JSON
            $value = Json::decode($value);
        } catch (InvalidArgumentException) {
            // Do nothing
        }

        // Normalize boolean values
        if (in_array($value, ['true', 'false'], true)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $value;
    }

    private static function keyToPath(string $key): array
    {
        return ArrayHelper::getColumn(
            explode(self::DEPTH_SEPARATOR, $key),
            function ($path) {
                return Inflector::variablize(strtolower($path));
            },
        );
    }
}
