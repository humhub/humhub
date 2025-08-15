<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour;

use Yii;
use yii\helpers\Url;

/**
 * Configurations for the introduction Tour
 * Loads and manage the configuration files
 * Default files are in @tour/config
 *
 * @since 1.18
 */
class TourConfig
{
    public const TOUR_ID_DASHBOARD = 'interface';
    public const TOUR_ID_SPACES = 'spaces';
    public const TOUR_ID_PROFILE = 'profile';
    public const TOUR_ID_ADMINISTRATION = 'administration';
    /**
     * string
     * A unique ID of the Tour
     */
    public const KEY_TOUR_ID = 'tour_id';
    /**
     * bool
     * Optional. Set to false for hiding the Tour from the list
     */
    public const KEY_IS_VISIBLE = 'is_visible';
    /**
     * string
     * The title of the Tour displayed in the widget
     */
    public const KEY_TITLE = 'title';
    /**
     * string|null
     * Optional The Controller class of the Tour where it must be started
     * If null or not defined, the KEY_START_URL will be used instead
     */
    public const KEY_TOUR_ON_CONTROLLER_CLASS = 'tour_on_controller_class';
    /**
     * string
     * Starting URL of the Tour
     */
    public const KEY_START_URL = 'start_url';
    /**
     * string|null
     * ID of the next Tour to show when the current the Tour is finished
     */
    public const KEY_NEXT_TOUR_ID = 'next_tour_id';
    /**
     * array
     * Driver.js configuration and steps
     * Available values: https://driverjs.com/docs/
     */
    public const KEY_DRIVER_JS = 'driver_js';

    /**
     * Return an array of valid and visible Tour configs
     * The array keys are the Tour IDs
     */
    public static function get(): array
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('tour');

        $tourConfigs = [];
        foreach ($module->tourConfigFiles as $file) {
            $config = require Yii::getAlias($file);
            if (static::isValidConfig($config) && static::getIsVisible($config)) {
                $tourConfigs[$config[self::KEY_TOUR_ID]] = $config;
            }
        }

        return $tourConfigs;
    }

    public static function isValidConfig($config): bool
    {
        $isValid
            = is_array($config)
            && !empty($config[self::KEY_TOUR_ID])
            && !empty($config[self::KEY_TITLE])
            && !empty($config[self::KEY_START_URL])
            && array_key_exists(self::KEY_NEXT_TOUR_ID, $config)
            && !empty($config[self::KEY_DRIVER_JS]);

        if (!$isValid) {
            Yii::error("Invalid Tour params: " . print_r($config, true), 'tour');
        }

        return $isValid;
    }

    public static function getCurrent(): ?array
    {
        foreach (self::get() as $config) {
            $requiredControllerClass = static::getRequiredControllerClass($config);
            if (
                ($requiredControllerClass && Yii::$app->controller instanceof $requiredControllerClass)
                || Url::current() === static::getStartUrl($config)
            ) {
                return $config;
            }
        }

        return null;
    }

    public static function IsCurrentRouteAcceptable(string $tourId): bool
    {
        return array_key_exists($tourId, self::get());
    }

    private static function getConfigValue(array $config, string $key): mixed
    {
        if (array_key_exists($key, $config)) {
            $value = $config[$key] ?? null;
            return is_callable($value) ? $value() : $value;
        }
        return null;
    }

    public static function getTourId(array $config): string
    {
        return static::getConfigValue($config, self::KEY_TOUR_ID);
    }

    /**
     * True if not defined
     */
    public static function getIsVisible(array $config): bool
    {
        return static::getConfigValue($config, self::KEY_IS_VISIBLE) ?? true;
    }

    public static function getTitle(array $config): string
    {
        return static::getConfigValue($config, self::KEY_TITLE);
    }

    public static function getRequiredControllerClass(array $config): ?string
    {
        return static::getConfigValue($config, self::KEY_TOUR_ON_CONTROLLER_CLASS);
    }

    public static function getStartUrl(array $config): string
    {
        return static::getConfigValue($config, self::KEY_START_URL);
    }

    public static function getNextUrl(array $config): ?string
    {
        if (!$config[self::KEY_NEXT_TOUR_ID]) {
            return null;
        }

        $nextTourId = static::getConfigValue($config, self::KEY_NEXT_TOUR_ID);
        if (!$nextTourId) {
            return null;
        }

        $nextConfig = static::get()[$nextTourId];
        if (!$nextConfig) {
            return null;
        }

        return static::getStartUrl($nextConfig);
    }

    public static function getDriverJs(array $config): array
    {
        return static::getConfigValue($config, self::KEY_DRIVER_JS);
    }
}
