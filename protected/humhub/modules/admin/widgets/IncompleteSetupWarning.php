<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Widget;
use humhub\modules\admin\Module;
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

    const PROBLEM_QUEUE_RUNNER = 'queue-runner';
    const PROBLEM_CRON_JOBS = 'cron-jobs';


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
            'problems' => $problems
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

        return $problems;
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

}
