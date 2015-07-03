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

    const CACHE_ID = 'module_configs';

    public function bootstrap($app)
    {

        $modules = Yii::$app->cache->get(self::CACHE_ID);

        if ($modules === false) {
            $modules = [];
            foreach (array(Yii::getAlias('@app/modules'), Yii::getAlias('@humhub/core')) as $modulePath) {
                foreach (scandir($modulePath) as $moduleId) {
                    if ($moduleId == '.' || $moduleId == '..')
                        continue;

                    $moduleDir = $modulePath . DIRECTORY_SEPARATOR . $moduleId;
                    if (is_dir($moduleDir) && is_file($moduleDir . DIRECTORY_SEPARATOR . 'config.php')) {
                        try {
                            $modules[$moduleDir] = require($moduleDir . DIRECTORY_SEPARATOR . 'config.php');
                        } catch (Exception $ex) {
                            
                        }
                    }
                }
            }
            Yii::$app->cache->set(self::CACHE_ID, $modules);
        }

        Yii::$app->moduleManager->registerBulk($modules);
    }

}
