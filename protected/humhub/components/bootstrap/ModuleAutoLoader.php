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
use humhub\services\ModuleDiscoveryService;
use ReflectionClass;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;

/**
 * ModuleAutoLoader bootstraps the module system by delegating discovery to
 * {@see ModuleDiscoveryService} and registration to {@see \humhub\components\ModuleManager}.
 *
 * **Console commands without module dependencies** ({@see WithoutModuleAutoload}):
 * Controllers annotated with `#[WithoutModuleAutoload]` only load core modules, skipping
 * third-party module configs. Use for commands that must run cleanly during upgrades when
 * external module configs may reference removed core classes (e.g. `migrate/up`, `settings/*`).
 *
 * @author luke
 */
class ModuleAutoLoader implements BootstrapInterface
{
    /**
     * @deprecated since 1.19 — use {@see ModuleDiscoveryService::CONFIGURATION_FILE}
     */
    public const CONFIGURATION_FILE = ModuleDiscoveryService::CONFIGURATION_FILE;

    /**
     * @param Application $app the application currently running
     * @throws InvalidConfigException
     * @throws ErrorException
     */
    public function bootstrap($app)
    {
        if (!$app->request->isConsoleRequest
            && !$app->installationState->hasState(InstallationState::STATE_DATABASE_CREATED)) {
            EnvironmentChecker::preInstallChecks();
        }

        if (!$app->request->isConsoleRequest) {
            $modules = ModuleDiscoveryService::locateModuleConfigs();
            Yii::$app->moduleManager->registerBulk($modules);
            return;
        }

        // Console: always register core modules first so their consoleControllerMap entries
        // land in Yii::$app->controllerMap before hasWithoutModuleAutoloadAttribute() reads it
        $coreModules = ModuleDiscoveryService::loadModuleConfigs([ModuleDiscoveryService::CORE_MODULE_PATH]);
        Yii::$app->moduleManager->registerBulk($coreModules);

        if (self::hasWithoutModuleAutoloadAttribute()) {
            return;
        }

        // Normal console command: also load third-party modules
        $thirdPartyPaths = array_values(array_filter(
            Yii::$app->params['moduleAutoloadPaths'] ?? [],
            fn($path) => $path !== ModuleDiscoveryService::CORE_MODULE_PATH,
        ));

        if (!empty($thirdPartyPaths)) {
            $thirdPartyModules = ModuleDiscoveryService::loadModuleConfigs($thirdPartyPaths);
            Yii::$app->moduleManager->registerBulk($thirdPartyModules);
        }
    }

    /**
     * Returns true if the current console command's controller class is annotated
     * with {@see WithoutModuleAutoload}.
     *
     * Only returns true when the database exists — broken module configs can only
     * occur on an installed system, not during initial setup or CI.
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

        return !empty((new ReflectionClass($controllerClass))->getAttributes(WithoutModuleAutoload::class));
    }
}
