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
use yii\base\ErrorException;
use yii\base\InvalidArgumentException;
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
     * @return array
     * @throws ErrorException
     */
    public static function locateModules()
    {
        $modules = Yii::$app->cache->get(self::CACHE_ID);

        if ($modules === false || YII_DEBUG) {
            $modules = static::findModules(Yii::$app->params['moduleAutoloadPaths']);
            Yii::$app->cache->set(self::CACHE_ID, $modules);
        }

        return $modules;
    }

    /**
     * Find all modules with configured paths
     * @param array $paths
     * @return array
     * @throws ErrorException
     */
    private static function findModules($paths)
    {
        $folders = [];
        foreach ($paths as $path) {
            try {
                $folders = array_merge($folders, self::findModulesByPath($path));
            } catch (InvalidArgumentException $ex) {
                throw new ErrorException('Invalid module autoload path: ' . $path);
            }
        }

        $modules = [];
        $moduleIdFolders = [];
        foreach ($folders as $folder) {
            try {
                /** @noinspection PhpIncludeInspection */
                $moduleConfig = include $folder . DIRECTORY_SEPARATOR . self::CONFIGURATION_FILE;
                if (Yii::$app->moduleManager->preventDuplicatedModules && isset($moduleIdFolders[$moduleConfig['id']])) {
                    Yii::error('Duplicated module "' . $moduleConfig['id'] . '"(' . $folder . ') is already loaded from the folder "' . $moduleIdFolders[$moduleConfig['id']] . '"');
                } else {
                    $modules[$folder] = $moduleConfig;
                    $moduleIdFolders[$moduleConfig['id']] = $folder;
                }
            } catch (\Throwable $e) {
                Yii::error($e);
            }
        }

        if (Yii::$app->moduleManager->preventDuplicatedModules) {
            // Overwrite module paths from config
            foreach (Yii::$app->moduleManager->overwriteModuleBasePath as $overwriteModuleId => $overwriteModulePath) {
                if (isset($moduleIdFolders[$overwriteModuleId]) && $moduleIdFolders[$overwriteModuleId] != $overwriteModulePath) {
                    try {
                        $moduleConfig = include $overwriteModulePath . DIRECTORY_SEPARATOR . self::CONFIGURATION_FILE;
                        Yii::info('Overwrite path of the module "' . $overwriteModuleId . '" to the folder "' . $overwriteModulePath . '"');
                        // Remove original config
                        unset($modules[$moduleIdFolders[$overwriteModuleId]]);
                        // Use config from the overwritten path
                        $modules[$overwriteModulePath] = $moduleConfig;
                        $moduleIdFolders[$overwriteModuleId] = $overwriteModulePath;
                    } catch (\Throwable $e) {
                        Yii::error($e);
                    }
                }
            }
        }

        return $modules;
    }

    /**
     * Find all directories with a configuration file inside
     * @param string $path
     * @return array
     * @throws InvalidArgumentException
     */
    private static function findModulesByPath($path)
    {
        $hasConfigurationFile = function ($path) {
            return is_file($path . DIRECTORY_SEPARATOR . self::CONFIGURATION_FILE);
        };

        return FileHelper::findDirectories(
            Yii::getAlias($path, true),
            ['filter' => $hasConfigurationFile, 'recursive' => false]
        );
    }
}
