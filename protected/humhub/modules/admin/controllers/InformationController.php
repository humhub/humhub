<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
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
    public $adminOnly = false;

    /**
     * @inheritdoc
     */
    public $defaultAction = 'about';

    public function init()
    {
        $this->subLayout = '@admin/views/layouts/information';
        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => \humhub\modules\admin\permissions\SeeAdminInformation::className()]
        ];
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

        return $this->render('about', [
            'currentVersion' => Yii::$app->version,
            'latestVersion' => $latestVersion,
            'isNewVersionAvailable' => $isNewVersionAvailable,
            'isUpToDate' => $isUpToDate
        ]);
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
        $currentUser = '';
        if (function_exists('get_current_user')) {
            $currentUser = get_current_user();
        }

        $lastRunHourly = Yii::$app->settings->getUncached('cronLastHourlyRun');
        $lastRunDaily = Yii::$app->settings->getUncached('cronLastDailyRun');


        return $this->render('cronjobs', [
            'lastRunHourly' => $lastRunHourly,
            'lastRunDaily' => $lastRunDaily,
            'currentUser' => $currentUser
        ]);
    }

}
