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

/**
 * Enhanced MigrateController which also allows the includeMOduleMigration option
 * to migrate up all enabled modules simultaneously.
 * 
 * @author Luke
 */
class CronController extends \yii\console\controller
{

    const EVENT_ON_HOURLY_RUN = "hourly";
    const EVENT_ON_DAILY_RUN = "daily";
    const EVENT_ON_WEEKLY_RUN = "weekly";

}
