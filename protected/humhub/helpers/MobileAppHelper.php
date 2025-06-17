<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\helpers;

use humhub\modules\file\Module;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;

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

    public static function getFileUploadSettings(): void
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('file');

        $json = [
            'type' => 'fileUploadSettings',
            'fileUploadUrl' => Url::to(['/file/file/upload'], true),
            'contentCreateUrl' => Url::to(['/file/share-intend/index'], true),
            'maxFileSize' => $module->settings->get('maxFileSize'),
            'allowedExtensions' => $module->settings->get('allowedExtensions'),
            'imageMaxResolution' => $module->imageMaxResolution,
            'imageJpegQuality' => $module->imageJpegQuality,
            'imagePngCompressionLevel' => $module->imagePngCompressionLevel,
            'imageWebpQuality' => $module->imageWebpQuality,
            'imageMaxProcessingMP' => $module->imageMaxProcessingMP,
            'denyDoubleFileExtensions' => $module->denyDoubleFileExtensions,
        ];

        $message = Json::encode($json);
        self::sendFlutterMessage($message);
    }

    protected static function sendFlutterMessage($msg): void
    {
        Yii::$app->view->registerJs('if (window.flutterChannel) { window.flutterChannel.postMessage(\'' . $msg . '\'); }');
    }
}
