<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\helpers\DeviceDetectorHelper;
use humhub\helpers\MobileAppHelper;
use humhub\modules\admin\models\forms\MobileSettingsForm;
use humhub\modules\file\Module;
use Yii;
use yii\helpers\Url;

/**
 * @since 1.18.0
 */
class MobileAppController extends Controller
{
    public $access = ControllerAccess::class;

    public function actionInstanceOpener()
    {
        MobileAppHelper::registerShowOpenerScript();
        Yii::$app->view->registerJs('window.location.href = "' . Url::home() . '";');
        return $this->renderContent('');
    }

    public function actionGetSettings()
    {
        if (!DeviceDetectorHelper::isAppRequest()) {
            $this->forbidden();
        }

        /* @var Module $module */
        $module = Yii::$app->getModule('file');

        $mobileSettingsForm = new MobileSettingsForm();

        $settings = [
            'appName' => Yii::$app->name,
            'appVersion' => Yii::$app->version,
            'fileUploadSettings' => [
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
            ],
            'whiteListedDomains' => $mobileSettingsForm->getWhiteListedDomainsArray(),
        ];

        return $this->asJson($settings);
    }
}
