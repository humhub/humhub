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

    public function actionIndex()
    {
        $this->stdout("Executing database integrity checks:\n\n", Console::FG_YELLOW);
        $this->trigger(self::EVENT_ON_RUN);
        $this->stdout("Integrity checks done.\n\n", Console::FG_GREEN);
        return self::EXIT_CODE_NORMAL;
    }

    public function showTestHeadline($headline)
    {
        $this->stdout("Checking: " . $headline . "\n", Console::FG_GREEN);
    }

    public function showFix($message)
    {
        if (!$this->interactive) {
            $this->stdout($message."\n");
            return true;
        }
        
        return $this->confirm($message);
    }

}
