<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\helpers;

use Detection\Exception\MobileDetectException;
use Detection\MobileDetect;
use Yii;

/**
 * @since 1.17
 */
class DeviceDetectorHelper
{
    public static function isMobile(): bool
    {
        try {
            return (bool)static::getMobileDetect()?->isMobile();
        } catch (MobileDetectException $e) {
            Yii::error('DeviceDetectorHelper::isMobile() error: ' . $e->getMessage());
            return false;
        }
    }

    public static function isTablet(): bool
    {
        try {
            return (bool)static::getMobileDetect()?->isTablet();
        } catch (MobileDetectException $e) {
            Yii::error('DeviceDetectorHelper::isTablet() error: ' . $e->getMessage());
            return false;
        }
    }

    private static function getMobileDetect(): ?MobileDetect
    {
        $userAgent = Yii::$app->request->getUserAgent();
        if (!$userAgent) {
            return null;
        }

        $detect = new MobileDetect();
        $detect->setUserAgent($userAgent);
        return $detect;
    }

    public static function isAppRequest(): bool
    {
        return
            Yii::$app->request->headers->get('x-requested-with', null, true) === 'com.humhub.app'
            || Yii::$app->request->headers->has('x-humhub-app');
    }

    /**
     * Determines whether the app is a branded app with custom firebase configuration.
     * @return bool
     */
    public static function isAppWithCustomFcm(): bool
    {
        return
            static::isAppRequest()
            && Yii::$app->request->headers->has('x-humhub-app-bundle-id')
            && !str_contains(
                Yii::$app->request->headers->get('x-humhub-app-bundle-id', '', true),
                'com.humhub.app',
            );
    }

    public static function isIosApp(): bool
    {
        return
            static::isAppRequest()
            && Yii::$app->request->headers->get('x-humhub-app-is-ios');
    }

    public static function isAndroidApp(): bool
    {
        return
            static::isAppRequest()
            && Yii::$app->request->headers->get('x-humhub-app-is-android');
    }

    /**
     * True if the mobile app can support multiple HumHub instances.
     * Requires HumHub mobile app v1.0.124 or later.
     */
    public static function isMultiInstanceApp(): bool
    {
        return
            static::isAppRequest()
            && Yii::$app->request->headers->get('x-humhub-app-is-multi-instance');
    }

    /**
     * True if the mobile app Opener landing page is visible and should be hidden.
     * Requires HumHub mobile app v1.0.124 or later.
     */
    public static function appOpenerState(): bool
    {
        return
            static::isAppRequest()
            && Yii::$app->request->headers->get('x-humhub-app-opener-state');
    }

    public static function isMicrosoftOffice(): bool
    {
        return str_contains((string)Yii::$app->request->getUserAgent(), 'Microsoft Office');
    }

    public static function getBodyClasses(): array
    {
        $classes = [];

        if (static::isAppRequest()) {
            $classes[] = 'device-mobile-app';
            if (static::isIosApp()) {
                $classes[] = 'device-ios-mobile-app';
            } elseif (static::isAndroidApp()) {
                $classes[] = 'device-android-mobile-app';
            }
        } elseif (static::isMobile()) {
            $classes[] = 'device-mobile';
        } elseif (static::isTablet()) {
            $classes[] = 'device-tablet';
        } else {
            $classes[] = 'device-desktop';
        }

        return $classes;
    }
}
