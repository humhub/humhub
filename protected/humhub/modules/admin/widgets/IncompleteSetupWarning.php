<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Widget;
use humhub\helpers\DeviceDetectorHelper;
use humhub\libs\SelfTest;
use humhub\modules\admin\Module;
use humhub\services\DocumentRootProbeService;
use humhub\services\DocumentRootService;
use humhub\widgets\bootstrap\Link;
use Yii;
use yii\db\Query;
use yii\queue\db\Queue;

/**
 * IncompleteSetupWarning shows a snippet in the dashboard
 * if problems of the HumHub setup were found.
 *
 * @package humhub\modules\admin\widgets
 */
class IncompleteSetupWarning extends Widget
{
    public const PROBLEM_QUEUE_RUNNER = 'queue-runner';
    public const PROBLEM_CRON_JOBS = 'cron-jobs';
    public const PROBLEM_MOBILE_APP_PUSH_SERVICE = 'mobile-app-push-service';
    public const PROBLEM_LEGACY_ENTRY_SCRIPT = 'legacy-entry-script';
    public const PROBLEM_DOCUMENT_ROOT_EXPOSED = 'document-root-exposed';


    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (!Yii::$app->user->isAdmin()) {
            return;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('admin');

        if (!$module->showDashboardIncompleteSetupWarning) {
            return;
        }


        $problems = $this->getProblems();

        if (count($problems) === 0) {
            return;
        }

        return $this->render('incomplete-setup-warning', [
            'problems' => $problems,
        ]);
    }


    /**
     * Returns an array with found problem keys
     *
     * @return array
     */
    protected function getProblems()
    {
        $problems = [];

        if (!$this->checkQueue()) {
            $problems[] = static::PROBLEM_QUEUE_RUNNER;
        }

        if (!$this->checkCron()) {
            $problems[] = static::PROBLEM_CRON_JOBS;
        }

        if (DeviceDetectorHelper::isAppRequest() && !SelfTest::isPushModuleAvailable()) {
            $problems[] = static::PROBLEM_MOBILE_APP_PUSH_SERVICE;
        }

        return [...$problems, ...static::getDocumentRootProblems()];
    }

    /**
     * Problems with how the web server is wired to the installation.
     *
     * Both cases mean the same thing - the installation root is served - so only the one naming the
     * actual cause is reported; two bullets asking for the same move would be noise. An
     * installation that cannot reach itself yields nothing here: that is a verification gap rather
     * than a finding, and the prerequisites list is where it belongs.
     *
     * For a correctly served installation this costs no HTTP request: the probe only asks anything
     * when the site is reached under the document root's directory name.
     *
     * @return string[]
     * @since 1.20
     */
    public static function getDocumentRootProblems(
        ?DocumentRootService $documentRoot = null,
        ?DocumentRootProbeService $probe = null,
    ): array {
        $documentRoot ??= DocumentRootService::instance();

        if ($documentRoot->isLegacyEntryScript()) {
            return [static::PROBLEM_LEGACY_ENTRY_SCRIPT];
        }

        $probe ??= DocumentRootProbeService::instance();

        return $probe->getState() === DocumentRootProbeService::STATE_EXPOSED
            ? [static::PROBLEM_DOCUMENT_ROOT_EXPOSED]
            : [];
    }

    /**
     * @return bool queue worker status
     */
    protected function checkQueue()
    {

        // Only for database queue
        if (Yii::$app->queue instanceof Queue) {
            /** @var Queue $queue */
            $queue = Yii::$app->queue;

            $time = time() - 60 * 20;

            $counter = (new Query())
                ->select('count(*) as jobCount')
                ->from($queue->tableName)
                ->andWhere(['channel' => $queue->channel, 'reserved_at' => null])
                ->andWhere('[[pushed_at]] <= :time - delay', [':time' => $time])
                ->one($queue->db);


            if (is_array($counter) && array_key_exists('jobCount', $counter) && $counter['jobCount'] > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool cron status
     */
    protected function checkCron()
    {
        $lastRun = (int)Yii::$app->settings->getUncached('cronLastRun');
        if (empty($lastRun) || $lastRun < time() - 60 * 60) {
            return false;
        }

        return true;
    }

    public static function docBtn(string $url): string
    {
        if (!Yii::$app->user->isAdmin()) {
            return '';
        }
        return Link::to(Yii::t('AdminModule.base', 'Open documentation'), $url)
            ->icon('external-link')
            ->loader(false)
            ->options(['target' => '_blank'])
            ->sm();
    }
}
