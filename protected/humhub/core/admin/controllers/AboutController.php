<?php

namespace humhub\core\admin\controllers;

use Yii;
use humhub\components\Controller;

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

        if ($this->getModule()->marketplaceEnabled) {
            $onlineModuleManager = new OnlineModuleManager();
            $latestVersion = $onlineModuleManager->getLatestHumHubVersion();
            if ($latestVersion) {
                $isNewVersionAvailable = version_compare($latestVersion, HVersion::VERSION, ">");
                $isUpToDate = !$isNewVersionAvailable;
            }
        }

        $this->render('index', array(
            'currentVersion' => HVersion::VERSION,
            'latestVersion' => $latestVersion,
            'isNewVersionAvailable' => $isNewVersionAvailable,
            'isUpToDate' => $isUpToDate
        ));
    }

}
