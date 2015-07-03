<?php

namespace humhub\modules\admin\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\admin\libs\OnlineModuleManager;

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class AboutController extends Controller
{

    public $subLayout = "/_layout";

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'adminOnly' => true
            ]
        ];
    }

    public function actionIndex()
    {
        $isNewVersionAvailable = false;
        $isUpToDate = false;
        $latestVersion = "";

        if ($this->module->marketplaceEnabled) {
            $onlineModuleManager = new OnlineModuleManager();
            $latestVersion = $onlineModuleManager->getLatestHumHubVersion();
            if ($latestVersion) {
                $isNewVersionAvailable = version_compare($latestVersion, Yii::$app->version, ">");
                $isUpToDate = !$isNewVersionAvailable;
            }
        }

        return $this->render('index', array(
                    'currentVersion' => Yii::$app->version,
                    'latestVersion' => $latestVersion,
                    'isNewVersionAvailable' => $isNewVersionAvailable,
                    'isUpToDate' => $isUpToDate
        ));
    }

}
