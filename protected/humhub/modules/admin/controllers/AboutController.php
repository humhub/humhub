<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use humhub\modules\admin\libs\OnlineModuleManager;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\libs\HumHubAPI;

/**
 * AboutController shows informations about the HumHub installation
 * 
 * @since 0.5
 */
class AboutController extends Controller
{

    public function actionIndex()
    {
        $isNewVersionAvailable = false;
        $isUpToDate = false;

        $latestVersion = HumHubAPI::getLatestHumHubVersion();
        if ($latestVersion) {
            $isNewVersionAvailable = version_compare($latestVersion, Yii::$app->version, ">");
            $isUpToDate = !$isNewVersionAvailable;
        }

        return $this->render('index', array(
                    'currentVersion' => Yii::$app->version,
                    'latestVersion' => $latestVersion,
                    'isNewVersionAvailable' => $isNewVersionAvailable,
                    'isUpToDate' => $isUpToDate
        ));
    }

}
