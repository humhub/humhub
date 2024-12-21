<?php

namespace humhub\helpers;

use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;

class EnvHelper
{
    private const FIXED_SETTING_PREFIX = 'HUMHUB_FIXED_SETTINGS';
    private const MAIN_PREFIX = 'HUMHUB_CONFIG';
    private const WEB_PREFIX = 'HUMHUB_WEB_CONFIG';
    private const CLI_PREFIX = 'HUMHUB_CLI_CONFIG';
    private const FIXED_SETTINGS_PATH = ['params', 'fixed-settings'];
    private const DEPTH_SEPARATOR = '__';
    private const ALIASES_PREFIX = 'HUMHUB_ALIASES';

    public static function toConfig(?array $env = [], ?string $applicationType = null): array
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

            foreach (
                ArrayHelper::getValue([
                    \humhub\components\Application::class => [self::MAIN_PREFIX, self::WEB_PREFIX],
                    \humhub\components\console\Application::class => [self::MAIN_PREFIX, self::CLI_PREFIX],
                ], $applicationType, [self::MAIN_PREFIX]) as $prefix
            ) {
                if (StringHelper::startsWith($key, $prefix . self::DEPTH_SEPARATOR)) {
                    ArrayHelper::setValue(
                        $config,
                        [
                            ...self::keyToPath(str_replace($prefix . self::DEPTH_SEPARATOR, '', $key)),
                        ],
                        $value,
                    );
                }
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

    /**
     * Writes variables defined in ENV with the syntax `HUMHUB_ALIASES__ALIASNAME` to the config alias map.
     *
     * @param array $config
     * @return array
     */
    public static function resolveConfigAliases(array $config): array
    {
        if (!is_array($_ENV)) {
            return $config;
        }
        if (!is_array($config['aliases'])) {
            $config['aliases'] = [];
        }

        foreach ($_ENV as $key => $value) {
            if (StringHelper::startsWith($key, self::ALIASES_PREFIX . self::DEPTH_SEPARATOR)) {
                $aliasName = str_replace(self::ALIASES_PREFIX . self::DEPTH_SEPARATOR, '', $key);
                $config['aliases']['@' . strtolower($aliasName)] = $value;
            }
        }

        return $config;
    }
}
