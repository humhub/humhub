<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\helpers;

use Yii;

/**
 * @since 1.17
 */
class DeviceDetectorHelper
{
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

    public static function isMicrosoftOffice(): bool
    {
        return str_contains((string)Yii::$app->request->getUserAgent(), 'Microsoft Office');
    }
}
