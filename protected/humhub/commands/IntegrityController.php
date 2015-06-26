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
class IntegrityController extends \yii\console\controller
{

    const EVENT_ON_RUN = "run";

}
