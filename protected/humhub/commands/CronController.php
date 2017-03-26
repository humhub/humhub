<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use Yii;
use yii\console\Controller;
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
     * Executes hourly cron tasks.
     */
    public function actionHourly()
    {
        $this->stdout("Executing hourly tasks:\n\n", Console::FG_YELLOW);

        $this->trigger(self::EVENT_ON_HOURLY_RUN);

        Yii::$app->settings->set('cronLastHourlyRun', time());

		return self::EXIT_CODE_NORMAL;
    }

    /**
     * Executes daily cron tasks.
     */
    public function actionDaily()
    {
        $this->stdout("Executing daily tasks:\n\n", Console::FG_YELLOW);

        $this->trigger(self::EVENT_ON_DAILY_RUN);

        Yii::$app->settings->set('cronLastDailyRun', time());

		return self::EXIT_CODE_NORMAL;
    }

}
