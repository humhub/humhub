<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour\models;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\tour\Module;
use Yii;

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
     * A unique ID of the Tour
     */
    public const KEY_TOUR_ID = 'tour_id';
    /**
     * Optional. Set to false for hiding the Tour from the list
     */
    public const KEY_IS_VISIBLE = 'is_visible';
    /**
     * The title of the Tour displayed in the widget
     */
    public const KEY_TITLE = 'title';
    /**
     * The Controller class of the Tour where it must be started
     */
    public const KEY_REQUIRED_CONTROLLER_CLASS = 'required_controller_class';
    /**
     * Starting URL of the Tour
     */
    public const KEY_START_URL = 'start_url';
    /**
     * ID of the next Tour to show when the current the Tour is finished
     * Can be `null`
     */
    public const KEY_NEXT_TOUR_ID = 'next_tour_id';
    /**
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
            if (
                static::isValidConfig($config)
                && (!isset($config[self::KEY_IS_VISIBLE]) || $config[self::KEY_IS_VISIBLE])
            ) {
                $tourConfigs[$config[self::KEY_TOUR_ID]] = $config;
            }
        }

        return $tourConfigs;
    }

    public static function isValidConfig($config): bool
    {
        $isValid =
            is_array($config)
            && !empty($config[self::KEY_TOUR_ID])
            && !empty($config[self::KEY_TITLE])
            && !empty($config[self::KEY_REQUIRED_CONTROLLER_CLASS])
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
            if (Yii::$app->controller instanceof $config[self::KEY_REQUIRED_CONTROLLER_CLASS]) {
                return $config;
            }
        }

        return null;
    }

    public static function IsCurrentRouteAcceptable(string $tourId): bool
    {
        return array_key_exists($tourId, self::get());
    }


    public static function getTourSpace(): ?Space
    {
        $space = null;

        // Loop over all spaces where the user is member
        foreach (Membership::getUserSpaces() as $space) {
            if ($space->isAdmin() && !$space->isArchived()) {
                // If user is admin on this space, it´s the perfect match
                break;
            }
        }

        if ($space === null) {
            // If user is not member of any space, try to find a public space to run Tour in
            $space = Space::findOne(['and', ['!=', 'visibility' => Space::VISIBILITY_NONE], ['status' => Space::STATUS_ENABLED]]);
        }

        return $space;
    }

    public static function getNextUrl(?string $nextTourId): ?string
    {
        if (!$nextTourId) {
            return null;
        }

        return static::get()[$nextTourId][self::KEY_START_URL] ?? null;
    }
}
