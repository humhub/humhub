<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\commands\MigrateController;
use humhub\libs\SelfTest;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\components\DatabaseInfo;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\admin\permissions\SeeAdminInformation;
use humhub\modules\queue\driver\MySQL;
use humhub\modules\queue\helpers\QueueHelper;
use humhub\modules\queue\interfaces\QueueInfoInterface;
use humhub\modules\search\jobs\RebuildIndex;
use ReflectionClass;
use ReflectionException;
use Yii;

/**
 * Information
 *
 * @since 0.5
 */
class InformationController extends Controller
{
    public const DB_ACTION_CHECK = 0;
    public const DB_ACTION_RUN = 1;
    public const DB_ACTION_PENDING = 2;

    /**
     * @inheritdoc
     */
    public $adminOnly = false;

    /**
     * @inheritdoc
     */
    public $defaultAction = 'about';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->subLayout = '@admin/views/layouts/information';
        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['permissions' => SeeAdminInformation::class],
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
            'isUpToDate' => $isUpToDate,
        ]);
    }

    public function actionPrerequisites()
    {
        return $this->render('prerequisites', ['checks' => SelfTest::getResults()]);
    }

    public function actionDatabase(int $migrate = self::DB_ACTION_CHECK)
    {
        if ($migrate === self::DB_ACTION_RUN) {
            $migrationOutput = sprintf(
                "%s\n%s",
                MigrateController::webMigrateAll(),
                SettingController::flushCache()
            );
        } else {
            $migrationOutput = MigrateController::webMigrateAll(MigrateController::MIGRATION_ACTION_NEW);

            $migrate = str_contains($migrationOutput, 'No new migrations found.')
                ? self::DB_ACTION_CHECK
                : self::DB_ACTION_PENDING;
        }

        $databaseInfo = new DatabaseInfo(Yii::$app->db->dsn);

        $rebuildSearchJob = new RebuildIndex();
        if (Yii::$app->request->isPost && Yii::$app->request->get('rebuildSearch') == 1) {
            Yii::$app->queue->push($rebuildSearchJob);
        }

        return $this->render(
            'database',
            [
                'rebuildSearchRunning' => QueueHelper::isQueued($rebuildSearchJob),
                'databaseName' => $databaseInfo->getDatabaseName(),
                'migrationOutput' => $migrationOutput,
                'migrationStatus' => $migrate,
            ]
        );
    }

    /**
     * Caching Options
     */
    public function actionBackgroundJobs()
    {
        $lastRunHourly = (int)Yii::$app->settings->getUncached('cronLastHourlyRun');
        $lastRunDaily = (int)Yii::$app->settings->getUncached('cronLastDailyRun');

        $queue = Yii::$app->queue;

        $canClearQueue = false;
        if ($queue instanceof MySQL) {
            $canClearQueue = true;
            if (Yii::$app->request->isPost && Yii::$app->request->get('clearQueue') == 1) {
                $queue->clear();
                $this->view->setStatusMessage(
                    'success',
                    Yii::t('AdminModule.information', 'Queue successfully cleared.')
                );
            }
        }

        $waitingJobs = null;
        $delayedJobs = null;
        $doneJobs = null;
        $reservedJobs = null;

        if ($queue instanceof QueueInfoInterface) {
            /** @var QueueInfoInterface $queue */
            $waitingJobs = $queue->getWaitingJobCount();
            $delayedJobs = $queue->getDelayedJobCount();
            $doneJobs = $queue->getDoneJobCount();
            $reservedJobs = $queue->getReservedJobCount();
        }

        $driverName = null;
        try {
            $reflect = new ReflectionClass($queue);
            $driverName = $reflect->getShortName();
        } catch (ReflectionException $e) {
            Yii::error('Could not determine queue driver: ' . $e->getMessage());
        }

        return $this->render('background-jobs', [
            'lastRunHourly' => $lastRunHourly,
            'lastRunDaily' => $lastRunDaily,
            'waitingJobs' => $waitingJobs,
            'delayedJobs' => $delayedJobs,
            'doneJobs' => $doneJobs,
            'reservedJobs' => $reservedJobs,
            'driverName' => $driverName,
            'canClearQueue' => $canClearQueue
        ]);
    }
}
