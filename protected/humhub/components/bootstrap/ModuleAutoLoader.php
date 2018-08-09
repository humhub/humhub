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
use yii\helpers\FileHelper;

/**
 * ModuleAutoLoader automatically searches for config.php files in module folder an executes them.
 *
 * @author luke
 */
class ModuleAutoLoader implements BootstrapInterface
{
    const CACHE_ID = 'module_configs';
    const CONFIGURATION_FILE = 'config.php';

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
     * @deprecated 1.3 replace call for locateModules with findModules and handle caching outside of method (e.g. in boostrap)
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

    /**
     * Find all modules with configured paths
     * @param array $paths
     * @return array
     */
    public static function findModules($paths)
    {
        $folders = [];
        foreach ($paths as $path) {
            $folders = array_merge($folders, self::findModulesByPath($path));
        }

        $modules = [];
        foreach ($folders as $folder) {
            try {
                /** @noinspection PhpIncludeInspection */
                $modules[$folder] = require $folder . DIRECTORY_SEPARATOR . self::CONFIGURATION_FILE;
            } catch (\Exception $e) {
                Yii::error($e);
            }
        }

        return $modules;
    }

    /**
     * Find all directories with a configuration file inside
     * @param string $path
     * @return array
     */
    public static function findModulesByPath($path)
    {
        $hasConfigurationFile = function ($path) {
            return is_file($path . DIRECTORY_SEPARATOR . self::CONFIGURATION_FILE);
        };

        try {
            return FileHelper::findDirectories(Yii::getAlias($path, true), ['filter' => $hasConfigurationFile, 'recursive' => false]);
        } catch (yii\base\InvalidArgumentException $e) {
            return [];
        }
    }
}
