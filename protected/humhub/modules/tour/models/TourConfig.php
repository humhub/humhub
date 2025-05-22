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
 * Configurations for the introduction tour
 * Loads and manage the configuration files
 * Default files are in @tour/config
 *
 * @since 1.18
 */
class TourConfig
{
    public const PAGE_DASHBOARD = 'interface';
    public const PAGE_SPACES = 'spaces';
    public const PAGE_PROFILE = 'profile';
    public const PAGE_ADMINISTRATION = 'administration';
    /**
     * A unique name of the page
     */
    public const KEY_PAGE = 'page';
    /**
     * Optional. Set to false for hiding the page
     */
    public const KEY_IS_VISIBLE = 'is_visible';
    /**
     * The title of the page displayed in the widget
     */
    public const KEY_TITLE = 'title';
    /**
     * The Controller class of the page where the tour must be started
     */
    public const KEY_CONTROLLER_CLASS = 'controller_class';
    /**
     * URL of the page
     */
    public const KEY_URL = 'url';
    /**
     * Name of the page for the next page to show after the tour.
     * Can be `null`
     */
    public const KEY_NEXT_PAGE = 'next_page';
    /**
     * Driver.js configuration and steps
     * Available values: https://driverjs.com/docs/
     */
    public const KEY_DRIVER = 'driver';

    /**
     * Return an array of valid and visible page configs
     * The array keys are the Page names
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
                $tourConfigs[$config[self::KEY_PAGE]] = $config;
            }
        }

        return $tourConfigs;
    }

    public static function isValidConfig($config): bool
    {
        $isValid =
            is_array($config)
            && !empty($config[self::KEY_PAGE])
            && !empty($config[self::KEY_TITLE])
            && !empty($config[self::KEY_CONTROLLER_CLASS])
            && !empty($config[self::KEY_URL])
            && array_key_exists(self::KEY_NEXT_PAGE, $config)
            && !empty($config[self::KEY_DRIVER]);

        if (!$isValid) {
            Yii::error("Invalid Tour params: " . print_r($config, true), 'tour');
        }

        return $isValid;
    }

    public static function getCurrent(): ?array
    {
        foreach (self::get() as $config) {
            if (Yii::$app->controller instanceof $config[self::KEY_CONTROLLER_CLASS]) {
                return $config;
            }
        }

        return null;
    }

    public static function isPageAcceptable(string $page): bool
    {
        return array_key_exists($page, self::get());
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
            // If user is not member of any space, try to find a public space
            // to run tour in
            $space = Space::findOne(['and', ['!=', 'visibility' => Space::VISIBILITY_NONE], ['status' => Space::STATUS_ENABLED]]);
        }

        return $space;
    }

    public static function getNextUrl(?string $nextPage): ?string
    {
        if (!$nextPage) {
            return null;
        }

        return static::get()[$nextPage][self::KEY_URL] ?? null;
    }
}
