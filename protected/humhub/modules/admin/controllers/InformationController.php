<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\libs\SelfTest;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\components\DatabaseInfo;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\admin\permissions\SeeAdminInformation;
use humhub\modules\content\jobs\SearchRebuildIndex;
use humhub\modules\queue\driver\MySQL;
use humhub\modules\queue\helpers\QueueHelper;
use humhub\modules\queue\interfaces\QueueInfoInterface;
use humhub\services\MigrationService;
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
            'dockerImage' => $this->getDockerImageInfo(),
        ]);
    }

    /**
     * Returns the Docker image build information provided by the official HumHub Docker image
     * via the environment variables `HUMHUB_CONFIG__PARAMS__DOCKER__*`.
     *
     * @return array label => value, empty when not running in the Docker image
     * @since 1.19
     */
    private function getDockerImageInfo(): array
    {
        $params = Yii::$app->params['docker'] ?? null;
        if (!is_array($params)) {
            return [];
        }

        $labels = [
            'imageTag' => Yii::t('AdminModule.information', 'Image tag'),
            'imagePlatform' => Yii::t('AdminModule.information', 'Image platform'),
            'imageCreated' => Yii::t('AdminModule.information', 'Image build time'),
            'imageRevision' => Yii::t('AdminModule.information', 'Docker repository commit'),
            'coreRevision' => Yii::t('AdminModule.information', 'HumHub core commit'),
        ];

        $info = [];
        foreach ($labels as $key => $label) {
            if (isset($params[$key]) && is_scalar($params[$key]) && (string) $params[$key] !== '') {
                $info[$label] = (string) $params[$key];
            }
        }

        return $info;
    }

    public function actionPrerequisites()
    {
        return $this->render('prerequisites', ['checks' => SelfTest::getResults()]);
    }

    public function actionDatabase(int $migrate = MigrationService::DB_ACTION_CHECK)
    {
        $migrationService = MigrationService::create();

        $migrate = $migrationService->runAction($migrate);

        if ($migrate === MigrationService::DB_ACTION_SESSION) {
            return $this->redirect(['/admin/information/database']);
        }

        $databaseInfo = new DatabaseInfo(Yii::$app->db->dsn);

        $rebuildSearchJob = new SearchRebuildIndex();
        if (Yii::$app->request->isPost && Yii::$app->request->get('rebuildSearch') == 1) {
            Yii::$app->queue->push($rebuildSearchJob);
        }

        return $this->render(
            'database',
            [
                'rebuildSearchRunning' => QueueHelper::isQueued($rebuildSearchJob),
                'databaseName' => $databaseInfo->getDatabaseName(),
                'migrationOutput' => $migrationService->getLastMigrationOutput(),
                'migrationStatus' => $migrate,
            ],
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
                    Yii::t('AdminModule.information', 'Queue successfully cleared.'),
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
            'canClearQueue' => $canClearQueue,
        ]);
    }
}
