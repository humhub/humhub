<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use DateTime;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Cronjobs
 *
 * @author Luke
 */
class CronController extends Controller
{

    /**
     * @event Event an event that is triggered when the hourly cron is started.
     */
    const EVENT_ON_HOURLY_RUN = "hourly";

    /**
     * @event Event an event that is triggered when the daily cron is started.
     */
    const EVENT_ON_DAILY_RUN = "daily";


    /**
     * @var string mutex to acquire
     */
    const MUTEX_ID = 'cron-mutex';


    /**
     * Runs the cron jobs
     *
     * @return int status code
     */
    public function actionRun()
    {
        if (!Yii::$app->mutex->acquire(static::MUTEX_ID)) {
            $this->stdout("Cron execution skipped - already running!\n");
            return ExitCode::OK;
        }

        $this->runHourly();
        $this->runDaily();

        Yii::$app->settings->set('cronLastRun', time());

        Yii::$app->mutex->release(static::MUTEX_ID);
        return ExitCode::OK;
    }


    /**
     * Force run of the hourly cron jobs
     */
    public function actionHourly()
    {
        $this->stdout("Executing hourly tasks:\n\n", Console::FG_YELLOW);
        $this->runHourly(true);
        return ExitCode::OK;
    }


    /**
     * Force run of the daily cron jobs
     */
    public function actionDaily()
    {
        $this->stdout("Executing daily tasks:\n\n", Console::FG_YELLOW);
        $this->runDaily(true);
        return ExitCode::OK;
    }


    /**
     * Runs the hourly cron jobs
     *
     * @param bool $force
     */
    protected function runHourly($force = false)
    {
        $lastRun = (int)Yii::$app->settings->getUncached('cronLastHourlyRun');

        if (!empty($lastRun) && $force !== true) {
            // Execute only once a hour
            if (time() < $lastRun + 3600) {
                return;
            }
        }

        $this->trigger(self::EVENT_ON_HOURLY_RUN);
        Yii::$app->settings->set('cronLastHourlyRun', time());
    }

    /**
     * Runs the daily cron jobs
     *
     * @param bool $force
     */
    protected function runDaily($force = false)
    {
        $lastRun = (int)Yii::$app->settings->getUncached('cronLastDailyRun');

        if (!empty($lastRun) && $force !== true) {
            $lastTime = new DateTime('@' . $lastRun);
            $todayTime = DateTime::createFromFormat(
                'Y-m-d H:i',
                date('Y-m-d') . ' ' . Yii::$app->params['dailyCronExecutionTime']
            );
            $nowTime = new DateTime();

            // Already executed today time OR before today execution
            if ($lastTime >= $todayTime || $nowTime < $todayTime) {
                return;
            }
        }

        $this->trigger(self::EVENT_ON_DAILY_RUN);
        Yii::$app->settings->set('cronLastDailyRun', time());
    }
}
