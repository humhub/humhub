<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\bootstrap;

use humhub\components\Application;
use Yii;
use yii\base\BootstrapInterface;

/**
 * ModuleAutoLoader automatically searches for config.php files in module folder an executes them.
 *
 * @author luke
 */
class ModuleAutoLoader implements BootstrapInterface
{
    const CACHE_ID = 'module_configs';

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     * @throws \yii\base\InvalidConfigException
     */
    public function bootstrap($app)
    {
        $modules = self::locateModules();

        Yii::$app->moduleManager->registerBulk($modules);
    }

    /**
     * Find available modules
     * @return array|bool|mixed
     */
    public static function locateModules()
    {
        $modules = Yii::$app->cache->get(self::CACHE_ID);

        if ($modules === false) {
            $modules = [];
            foreach (Yii::$app->params['moduleAutoloadPaths'] as $modulePath) {
                $modulePath = Yii::getAlias($modulePath);
                foreach (scandir($modulePath) as $moduleId) {
                    if ($moduleId == '.' || $moduleId == '..') {
                        continue;
                    }

                    $moduleDir = $modulePath . DIRECTORY_SEPARATOR . $moduleId;
                    if (is_dir($moduleDir) && is_file($moduleDir . DIRECTORY_SEPARATOR . 'config.php')) {
                        try {
                            $modules[$moduleDir] = require($moduleDir . DIRECTORY_SEPARATOR . 'config.php');
                        } catch (\Exception $ex) {
                            Yii::error($ex);
                        }
                    }
                }
            }
            Yii::$app->cache->set(self::CACHE_ID, $modules);
        }

        return $modules;
    }
}
