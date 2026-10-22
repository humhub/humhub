<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\services;

use humhub\models\ModuleEnabled;
use Yii;
use yii\helpers\Json;

/**
 * Checks the modules a module depends on, as declared in its `module.json`:
 *
 * ```json
 * "humhub": {
 *     "minVersion": "1.19.1",
 *     "modules": {
 *         "clean-theme": { "minVersion": "2.5.1" }
 *     }
 * }
 * ```
 *
 * A dependency is met when the module is installed and enabled and, if a `minVersion` is given,
 * installed in at least that version.
 *
 * The check only reads metadata (the `module_enabled` table and the `module.json` files on disk),
 * no module class is loaded. So it gives the same result in the web application and in console
 * commands that do not load third-party modules ({@see \humhub\components\console\WithoutModuleAutoload}),
 * and it never brings an outdated module class into the process before that module is updated.
 *
 * @since 1.19.1
 */
class ModuleDependencyService
{
    /**
     * @var array<string, array{basePath: string, version: string|null, name: string|null}>|null
     */
    private ?array $installedModules = null;

    /**
     * @param array $moduleInfo the `module.json` contents of the module whose dependencies are checked
     * @param array<string, string> $plannedVersions versions installed modules will have once a pending
     * batch of updates is applied (moduleId => version), to check against that target state instead
     * of the current one
     */
    public function __construct(private readonly array $moduleInfo, private readonly array $plannedVersions = [])
    {
    }

    /**
     * Creates the service for the module installed at the given path.
     */
    public static function forModulePath(string $basePath): self
    {
        $moduleJsonPath = $basePath . DIRECTORY_SEPARATOR . 'module.json';

        try {
            $moduleInfo = is_file($moduleJsonPath) ? Json::decode(file_get_contents($moduleJsonPath)) : [];
        } catch (\Throwable) {
            $moduleInfo = [];
        }

        return new self(is_array($moduleInfo) ? $moduleInfo : []);
    }

    /**
     * Returns the declared dependencies.
     *
     * @return array<string, array{minVersion?: string}> map of moduleId => constraints
     */
    public function getDependencies(): array
    {
        return self::readDependencies($this->moduleInfo);
    }

    /**
     * Returns a message for each dependency that is not met, an empty array if all are met.
     *
     * @return string[]
     */
    public function getUnmetDependencies(): array
    {
        $messages = [];
        $enabledIds = ModuleEnabled::getEnabledIds();
        $coreModulePath = Yii::getAlias(ModuleDiscoveryService::CORE_MODULE_PATH) . DIRECTORY_SEPARATOR;

        foreach ($this->getDependencies() as $moduleId => $constraints) {
            $installed = $this->getInstalledModules()[$moduleId] ?? null;
            $moduleName = $installed['name'] ?? $moduleId;

            // Core modules are always enabled and have no entry in `module_enabled`
            $isEnabled = $installed !== null
                && (in_array($moduleId, $enabledIds, true) || str_starts_with($installed['basePath'], $coreModulePath));

            if (!$isEnabled) {
                $messages[] = Yii::t('base', 'You must first install and enable the {moduleName} module.', [
                    'moduleName' => $moduleName,
                ]);
                continue;
            }

            $version = $this->plannedVersions[$moduleId] ?? $installed['version'];
            if (isset($constraints['minVersion']) && version_compare((string)$version, $constraints['minVersion'], '<')) {
                $messages[] = Yii::t('base', 'You must first upgrade the {moduleName} module to version {version} or later.', [
                    'moduleName' => $moduleName,
                    'version' => $constraints['minVersion'],
                ]);
            }
        }

        return $messages;
    }

    /**
     * Registers the enabled dependencies that are not registered yet.
     *
     * Console commands without module autoload only register core modules, but a module's
     * `requirements.php` may still check its dependencies via `Yii::$app->getModule()`. Only the
     * declared dependencies are registered, after {@see getUnmetDependencies()} confirmed they are
     * installed in a suitable version. A dependency that cannot be registered (e.g. a stale config
     * referencing removed core classes) is skipped.
     */
    public function registerDependencies(): void
    {
        $dependencies = $this->getDependencies();
        $enabledIds = ModuleEnabled::getEnabledIds();

        foreach (ModuleDiscoveryService::locateModuleConfigs() as $basePath => $config) {
            $moduleId = $config['id'] ?? null;
            if (
                $moduleId === null
                || !isset($dependencies[$moduleId])
                || !in_array($moduleId, $enabledIds, true)
                || Yii::$app->moduleManager->hasModule($moduleId)
            ) {
                continue;
            }

            try {
                Yii::$app->moduleManager->register($basePath, $config);
            } catch (\Throwable $e) {
                Yii::warning('Could not load config for module "' . $moduleId . '": ' . $e->getMessage(), 'module');
            }
        }
    }

    /**
     * Orders modules so that each one comes after the modules it depends on.
     *
     * Only dependencies between the given modules are taken into account, otherwise the given
     * order is kept. Modules depending on each other in a cycle are all returned, in no particular
     * order among themselves.
     *
     * @param array<string, array> $moduleInfos map of moduleId => `module.json` contents
     * @return string[] the module ids in order
     */
    public static function sortByDependencies(array $moduleInfos): array
    {
        $sorted = [];
        $visiting = [];

        $visit = static function (string $moduleId) use (&$visit, &$sorted, &$visiting, $moduleInfos): void {
            if (isset($sorted[$moduleId]) || isset($visiting[$moduleId])) {
                return;
            }

            $visiting[$moduleId] = true;
            foreach (array_keys(self::readDependencies($moduleInfos[$moduleId])) as $dependencyId) {
                if (isset($moduleInfos[$dependencyId])) {
                    $visit($dependencyId);
                }
            }
            unset($visiting[$moduleId]);

            $sorted[$moduleId] = true;
        };

        foreach (array_keys($moduleInfos) as $moduleId) {
            $visit((string)$moduleId);
        }

        return array_map('strval', array_keys($sorted));
    }

    /**
     * @return array<string, array{minVersion?: string}>
     */
    private static function readDependencies(array $moduleInfo): array
    {
        $modules = $moduleInfo['humhub']['modules'] ?? [];
        if (!is_array($modules)) {
            return [];
        }

        $dependencies = [];
        foreach ($modules as $moduleId => $constraints) {
            if (!is_string($moduleId) || $moduleId === '') {
                continue;
            }

            $dependencies[$moduleId] = [];
            if (is_array($constraints) && isset($constraints['minVersion']) && is_scalar($constraints['minVersion'])) {
                $dependencies[$moduleId]['minVersion'] = (string)$constraints['minVersion'];
            }
        }

        return $dependencies;
    }

    private function getInstalledModules(): array
    {
        return $this->installedModules ??= ModuleDiscoveryService::findInstalledModules();
    }
}
