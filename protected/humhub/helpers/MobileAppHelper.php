<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\helpers;

use Yii;
use yii\helpers\Json;

/**
 * @since 1.18.0
 */
class MobileAppHelper
{
    public const SESSION_VAR_SHOW_OPENER = 'mobileAppShowOpener';

    public static function registerShowOpenerScript(): void
    {
        if (!DeviceDetectorHelper::isAppRequest()) {
            return;
        }

        $json = ['type' => 'showOpener'];
        $message = Json::encode($json);

        self::sendFlutterMessage($message);
    }

    protected static function sendFlutterMessage($msg): void
    {
        Yii::$app->view->registerJs('if (window.flutterChannel) { window.flutterChannel.postMessage(\'' . $msg . '\'); }');
    }
}
