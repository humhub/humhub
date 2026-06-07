<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\bootstrap;

use humhub\components\Application;
use humhub\components\console\WithoutModuleAutoload;
use humhub\components\InstallationState;
use humhub\modules\installer\libs\EnvironmentChecker;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\ErrorException;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;

/**
 * ModuleAutoLoader discovers and registers all available modules during application bootstrap.
 *
 * It scans all paths listed in the `moduleAutoloadPaths` application parameter for directories
 * containing a `config.php` file and passes the resulting configurations to
 * {@see \humhub\components\ModuleManager::registerBulk()}.
 *
 * **Console commands without module dependencies** ({@see WithoutModuleAutoload}):
 * Console controllers annotated with `#[WithoutModuleAutoload]` skip module loading entirely.
 * Use this for lightweight utility commands (e.g. `settings/set`, `cache/flush-all`) that must
 * run cleanly at any point in the application lifecycle, including during upgrades when external
 * module configs may reference removed core classes.
 *
 * @author luke
 */
class ModuleAutoLoader implements BootstrapInterface
{
    public const CACHE_ID = 'module_configs';
    public const CONFIGURATION_FILE = 'config.php';

    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     *
     * @throws InvalidConfigException Module a configuration does not have both an id and class attribute
     * @throws ErrorException On invalid module autoload path
     */
    public function bootstrap($app)
    {
        if (!$app->request->isConsoleRequest
            && !$app->installationState->hasState(InstallationState::STATE_DATABASE_CREATED)) {
            EnvironmentChecker::preInstallChecks();
        }

        if ($app->request->isConsoleRequest && self::hasWithoutModuleAutoloadAttribute()) {
            return;
        }

        $modules = self::locateModules();
        Yii::$app->moduleManager->registerBulk($modules);
    }

    /**
     * Returns true if the current console command's controller class is annotated
     * with {@see WithoutModuleAutoload}, indicating it does not require modules.
     *
     * Module loading is only skipped when the database is available — broken module
     * configs can only occur on an installed system, not during initial setup or CI
     * environments where the database has not yet been created.
     */
    private static function hasWithoutModuleAutoloadAttribute(): bool
    {
        if (!Yii::$app->installationState->hasState(InstallationState::STATE_DATABASE_CREATED)) {
            return false;
        }

        $route = $_SERVER['argv'][1] ?? '';
        $controllerId = explode('/', $route)[0];

        if (empty($controllerId)) {
            return false;
        }

        $map = Yii::$app->controllerMap[$controllerId] ?? null;
        $controllerClass = is_array($map)
            ? ($map['class'] ?? null)
            : ($map ?? (Yii::$app->controllerNamespace . '\\' . ucfirst($controllerId) . 'Controller'));

        if ($controllerClass === null || !class_exists($controllerClass)) {
            return false;
        }

        return !empty((new \ReflectionClass($controllerClass))->getAttributes(WithoutModuleAutoload::class));
    }

    /**
     * Find available modules
     *
     * @return array[] Array of module configurations with module ID as index.
     *          Read from cache if available and YII_DEBUG is disabled
     * @throws ErrorException On invalid module autoload path
     */
    public static function locateModules(): array
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
     *
     * @param string[] $paths
     *
     * @return array[] Array of module configurations with module ID as index
     * @throws ErrorException On invalid module autoload path
     */
    private static function findModules(iterable $paths): array
    {
        $folders = [];
        foreach ($paths as $path) {
            try {
                $folders = array_merge($folders, self::findModulesByPath($path));
            } catch (InvalidArgumentException) {
                throw new ErrorException('Invalid module autoload path: ' . $path);
            }
        }

        $modules = [];
        $moduleIdFolders = [];
        $preventDuplicatedModules = Yii::$app->moduleManager->preventDuplicatedModules;

        foreach ($folders as $folder) {
            try {
                $moduleConfig = static::getModuleConfigByPath($folder);
                if ($preventDuplicatedModules && isset($moduleIdFolders[$moduleConfig['id']])) {
                    Yii::error(
                        'Duplicated module "' . $moduleConfig['id'] . '"(' . $folder . ') is already loaded from the folder "' . $moduleIdFolders[$moduleConfig['id']] . '"',
                    );
                } else {
                    $modules[$folder] = $moduleConfig;
                    $moduleIdFolders[$moduleConfig['id']] = $folder;
                }
            } catch (\Throwable $e) {
                Yii::error($e);
            }
        }

        if ($preventDuplicatedModules) {
            // Overwrite module paths from config
            foreach (Yii::$app->moduleManager->overwriteModuleBasePath as $overwriteModuleId => $overwriteModulePath) {
                if (isset($moduleIdFolders[$overwriteModuleId]) && $moduleIdFolders[$overwriteModuleId] !== $overwriteModulePath) {
                    try {
                        $moduleConfig = static::getModuleConfigByPath($overwriteModulePath);

                        Yii::info(
                            'Overwrite path of the module "' . $overwriteModuleId . '" to the folder "' . $overwriteModulePath . '"',
                        );
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

    private static function getModuleConfigByPath(string $modulePath): array
    {
        return include $modulePath . DIRECTORY_SEPARATOR . self::CONFIGURATION_FILE;
    }


    /**
     * Find all directories with a configuration file inside
     *
     * @param string $path
     *
     * @return string[]
     * @throws InvalidArgumentException
     */
    private static function findModulesByPath(string $path): array
    {
        $hasConfigurationFile = (static fn($path) => is_file($path . DIRECTORY_SEPARATOR . self::CONFIGURATION_FILE));

        return FileHelper::findDirectories(
            Yii::getAlias($path, true),
            ['filter' => $hasConfigurationFile, 'recursive' => false],
        );
    }
}
