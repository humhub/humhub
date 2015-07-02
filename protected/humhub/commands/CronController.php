<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use humhub\models\Setting;

/**
 * CronCrontroller
 * 
 * @author Luke
 */
class CronController extends Controller
{

    const EVENT_ON_HOURLY_RUN = "hourly";
    const EVENT_ON_DAILY_RUN = "daily";

    public function actionHourly()
    {
        $this->stdout("Executing hourly tasks:\n\n", Console::FG_YELLOW);

        $this->trigger(self::EVENT_ON_HOURLY_RUN);

        $this->stdout("\n\nAll cron tasks finished.\n\n", Console::FG_GREEN);
        Setting::Set('cronLastHourlyRun', time());

        return self::EXIT_CODE_NORMAL;
    }

    public function actionDaily()
    {
        $this->stdout("Executing daily tasks:\n\n", Console::FG_YELLOW);

        $this->trigger(self::EVENT_ON_DAILY_RUN);

        $this->stdout("\n\nAll cron tasks finished.\n\n", Console::FG_GREEN);
        Setting::Set('cronLastDailyRun', time());

        return self::EXIT_CODE_NORMAL;
    }

    public function beginTask($taskName)
    {
        $this->stdout("\t* " . $taskName . ": ", Console::FG_GREY);
    }

    public function endTask()
    {
        $this->stdout(" OK!\n", Console::FG_GREEN);
    }

}
