<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\bootstrap;

use yii\base\BootstrapInterface;
use Yii;

/**
 * ModuleAutoLoader automatically searches for autostart.php files in module folder an executes them.
 *
 * @author luke
 */
class ModuleAutoLoader implements BootstrapInterface
{

    public function bootstrap($app)
    {
        foreach (array(Yii::getAlias('@app/modules'), Yii::getAlias('@humhub/core')) as $modulePath) {
            foreach (scandir($modulePath) as $moduleId) {
                if ($moduleId == '.' || $moduleId == '..')
                    continue;

                $moduleDir = $modulePath . DIRECTORY_SEPARATOR . $moduleId;
                if (is_dir($moduleDir) && is_file($moduleDir . DIRECTORY_SEPARATOR . 'autostart.php')) {
                    try {
                        require($moduleDir . DIRECTORY_SEPARATOR . 'autostart.php');
                    } catch (Exception $ex) {

                    }
                }
            }
        }
    }

}
