<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use humhub\components\Module;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Rector tool
 *
 * @author Luke
 */
class RectorController extends Controller
{

    /**
     * Runs core factorization
     *
     * @return int status code
     */
    public function actionCore(): int
    {
        return $this->process();
    }

    /**
     * Runs module factorization
     *
     * @param string $moduleId
     * @return int status code
     */
    public function actionModule(string $moduleId): int
    {
        /* @var Module $module */
        $module = Yii::$app->getModule($moduleId);

        if (!$module) {
            return $this->error('Module is not found!');
        }

        return $this->process($module->basePath);
    }

    private function error(string $message): int
    {
        $this->stdout('Error: ' . $message, Console::FG_RED);
        return ExitCode::UNAVAILABLE;
    }

    private function process(string $args = ''): int
    {
        $rector = Yii::getAlias('@webroot/protected/vendor/bin/rector');

        if (!file_exists($rector)) {
            return $this->error('Rector tool is not installed!');
        }

        $commands = [
            'cd ' . Yii::getAlias('@config'),
            'php ' . $rector . ' process ' . $args,
        ];

        echo shell_exec(implode(' && ', $commands));

        return ExitCode::OK;
    }
}
