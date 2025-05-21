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
 * Parameters for the introduction tour
 *
 * @since 1.18
 */
class TourParams
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

    public static function get(): array
    {
        return static::getCustom() ?? static::getDefault();
    }

    private static function getCustom(): ?array
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('tour');

        if (!is_array($module->customTourParams)) {
            return null;
        }

        $validCustomParams = [];
        foreach ($module->customTourParams as $params) {
            if (
                static::isValidParams($params)
                && (!isset($params[self::KEY_IS_VISIBLE]) || !$params[self::KEY_IS_VISIBLE])
            ) {
                $validCustomParams[] = $params;
            }
        }

        return $validCustomParams;
    }

    public static function getDefault(): array
    {
        $tourParams = [
            require(__DIR__ . '/../configs/tour-interface.php'),
            require(__DIR__ . '/../configs/tour-spaces.php'),
            require(__DIR__ . '/../configs/tour-profile.php'),
            require(__DIR__ . '/../configs/tour-administration.php'),
        ];

        foreach ($tourParams as $key => $param) {
            if (isset($params[self::KEY_IS_VISIBLE]) && !$params[self::KEY_IS_VISIBLE]) {
                unset($params[$key]);
            }
        }

        return $tourParams;
    }

    public static function isValidParams($params): bool
    {
        $isValid =
            is_array($params)
            && !empty($params[self::KEY_PAGE])
            && !empty($params[self::KEY_TITLE])
            && !empty($params[self::KEY_CONTROLLER_CLASS])
            && !empty($params[self::KEY_URL])
            && array_key_exists(self::KEY_NEXT_PAGE, $params)
            && !empty($params[self::KEY_DRIVER]);

        if (!$isValid) {
            Yii::error("Invalid Tour params: " . print_r($params, true), 'tour');
        }

        return $isValid;
    }

    public static function getCurrent(): ?array
    {
        foreach (self::get() as $params) {
            if (Yii::$app->controller instanceof $params[self::KEY_CONTROLLER_CLASS]) {
                return $params;
            }
        }

        return null;
    }

    public static function isPageAcceptable(string $page): bool
    {
        foreach (self::get() as $params) {
            if ($page === $params[self::KEY_PAGE]) {
                return true;
            }
        }

        return false;
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

    public static function getNextUrl(array $params): ?string
    {
        $nextPage = $params[self::KEY_NEXT_PAGE];
        if (!$nextPage) {
            return null;
        }

        foreach (static::get() as $searchedParams) {
            if ($searchedParams[self::KEY_PAGE] === $nextPage) {
                return $searchedParams[self::KEY_URL];
            }
        }

        return null;
    }
}
