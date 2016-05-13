<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\libs\HumHubAPI;

/**
 * Informations
 * 
 * @since 0.5
 */
class InformationController extends Controller
{

    /**
     * @inheritdoc
     */
    public $defaultAction = 'about';

    public function init()
    {
        $this->subLayout = '@admin/views/layouts/information';
        return parent::init();
    }

    public function actionAbout()
    {
        $this->appendPageTitle(Yii::t('AdminModule.base', 'About'));
        $isNewVersionAvailable = false;
        $isUpToDate = false;

        $latestVersion = HumHubAPI::getLatestHumHubVersion();
        if ($latestVersion) {
            $isNewVersionAvailable = version_compare($latestVersion, Yii::$app->version, ">");
            $isUpToDate = !$isNewVersionAvailable;
        }

        return $this->render('about', array(
                    'currentVersion' => Yii::$app->version,
                    'latestVersion' => $latestVersion,
                    'isNewVersionAvailable' => $isNewVersionAvailable,
                    'isUpToDate' => $isUpToDate
        ));
    }

    public function actionPrerequisites()
    {
        return $this->render('prerequisites', ['checks' => \humhub\libs\SelfTest::getResults()]);
    }

    public function actionDatabase()
    {
        return $this->render('database', ['migrate' => \humhub\commands\MigrateController::webMigrateAll()]);
    }

    /**
     * Caching Options
     */
    public function actionCronjobs()
    {
        return $this->render('cronjobs', array());
    }

}
