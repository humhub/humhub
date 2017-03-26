<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use yii\console\Controller;
use yii\helpers\Console;

/**
 * Performs data integrity checks.
 *
 * @author Luke
 */
class IntegrityController extends Controller
{

    /**
     * @event Event an event that is triggered when the integritychecker is started.
     */
    const EVENT_ON_RUN = "run";

    /**
     * @inheritdoc
     */
    public $defaultAction = 'index';

    /**
     * Starts integrity checker
     */
    public function actionRun()
    {
        $this->stdout("\n*** Performing database integrity checks\n\n", Console::FG_YELLOW);

		$this->trigger(self::EVENT_ON_RUN);

        $this->stdout("\n*** All integrity checks done\n\n", Console::FG_YELLOW);

		return self::EXIT_CODE_NORMAL;
    }

    /**
     * Shows a test headline
     *
     * @param string $headline
     */
    public function showTestHeadline($headline)
    {
        $this->stdout("Validating: ", Console::FG_GREEN);
        $this->stdout($headline . "\n", Console::FG_GREY);
    }

    /**
     * Shows a fix
     *
     * If not in interactive mode, it returns true otherwise a confirm dialog will be shown.
     *
     * @param string $headline
     * @return boolean
     */
    public function showFix($message)
    {
        if (!$this->interactive) {
            $this->stdout($message . "\n");
            return true;
        }

        return $this->confirm($message);
    }

    /**
     * Shows a warning
     *
     * @param string $message
     */
    public function showWarning($message)
    {
        $this->stdout("\tWarning: ", Console::FG_RED);
        $this->stdout($message . "\n", Console::FG_GREY);
    }

}
