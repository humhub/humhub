<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\services;

use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Json;

/**
 * ModuleDiscoveryService is the single place for all module discovery.
 *
 * It provides two tiers:
 *
 * **Safe discovery** (no config.php loaded) — used during upgrades when module configs may
 * reference core classes that have been removed:
 * - {@see scanPath()} — list subdirectories of an alias path
 * - {@see findMigrationPaths()} — locate module migration directories by filesystem convention
 * - {@see findInstalledModules()} — read module metadata from module.json files
 *
 * **Full config loading** — used during normal application bootstrap:
 * - {@see locateModuleConfigs()} — cached scan of all moduleAutoloadPaths, returns config.php arrays
 * - {@see loadModuleConfigs()} — uncached, loads config.php from the given paths
 *
 * @since 1.19
 */
class ModuleDiscoveryService
{
    public const CACHE_ID = 'module_configs';
    public const CONFIGURATION_FILE = 'config.php';
    public const CORE_MODULE_PATH = '@humhub/modules';

    // -------------------------------------------------------------------------
    // Safe discovery — no config.php loading
    // -------------------------------------------------------------------------

    /**
     * Returns all immediate subdirectories of a resolved alias path.
     *
     * Shared scanning primitive for all find* methods and for {@see ModuleAutoLoader}.
     * Returns an empty array for invalid or missing paths.
     *
     * @return string[] absolute directory paths
     */
    public static function scanPath(string $path): array
    {
        $resolvedPath = Yii::getAlias($path, false);
        if ($resolvedPath === false || !is_dir($resolvedPath)) {
            return [];
        }

        try {
            return FileHelper::findDirectories($resolvedPath, ['recursive' => false]);
        } catch (InvalidArgumentException) {
            return [];
        }
    }

    /**
     * Returns migration directory paths for all modules discovered by filesystem convention.
     *
     * Scans all paths listed in the `moduleAutoloadPaths` application parameter for
     * subdirectories containing a `migrations/` folder. The module ID is read from
     * `module.json`; directories without a valid `module.json` are skipped.
     * No module config.php is loaded.
     *
     * @param string $coreBasePath alias path to the core migration directory (e.g. '@humhub/migrations')
     * @return array<string, string> map of [moduleId => absoluteMigrationsPath], with key 'base' for core
     */
    public static function findMigrationPaths(string $coreBasePath): array
    {
        $paths = ['base' => $coreBasePath];

        foreach (Yii::$app->params['moduleAutoloadPaths'] as $autoloadPath) {
            foreach (self::scanPath($autoloadPath) as $moduleDir) {
                $migrationsDir = $moduleDir . DIRECTORY_SEPARATOR . 'migrations';
                if (!is_dir($migrationsDir)) {
                    continue;
                }

                $moduleId = self::readModuleId($moduleDir);
                if ($moduleId !== null) {
                    $paths[$moduleId] = $migrationsDir;
                }
            }
        }

        return $paths;
    }

    /**
     * Returns metadata for all installed modules by reading their module.json files.
     *
     * No module config.php is loaded. Modules without a module.json are ignored.
     *
     * @return array<string, array{basePath: string, version: string|null}> map of moduleId => metadata
     */
    public static function findInstalledModules(): array
    {
        $modules = [];

        foreach (Yii::$app->params['moduleAutoloadPaths'] as $autoloadPath) {
            foreach (self::scanPath($autoloadPath) as $moduleDir) {
                $moduleJsonPath = $moduleDir . DIRECTORY_SEPARATOR . 'module.json';
                if (!file_exists($moduleJsonPath)) {
                    continue;
                }

                try {
                    $info = Json::decode(file_get_contents($moduleJsonPath));
                    $moduleId = $info['id'] ?? null;
                    if ($moduleId !== null) {
                        $modules[$moduleId] = [
                            'basePath' => $moduleDir,
                            'version' => $info['version'] ?? null,
                        ];
                    }
                } catch (\Throwable) {
                    // malformed module.json — skip
                }
            }
        }

        return $modules;
    }

    /**
     * Returns the base path of an installed module, or null if not found.
     */
    public static function findModuleBasePath(string $moduleId): ?string
    {
        return self::findInstalledModules()[$moduleId]['basePath'] ?? null;
    }

    /**
     * Returns the installed version of a module read from its module.json, or null if not found.
     */
    public static function findInstalledVersion(string $moduleId): ?string
    {
        return self::findInstalledModules()[$moduleId]['version'] ?? null;
    }

    /**
     * Returns the module ID declared in a module's module.json, or null if the file is absent or malformed.
     */
    private static function readModuleId(string $modulePath): ?string
    {
        $moduleJsonPath = $modulePath . DIRECTORY_SEPARATOR . 'module.json';
        if (!file_exists($moduleJsonPath)) {
            return null;
        }

        try {
            $info = Json::decode(file_get_contents($moduleJsonPath));
            return $info['id'] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    // -------------------------------------------------------------------------
    // Full config loading — loads config.php
    // -------------------------------------------------------------------------

    /**
     * Returns all available module configurations, reading from cache when possible.
     *
     * Scans `moduleAutoloadPaths` and loads each module's config.php. The result is cached
     * unless {@see YII_DEBUG} is enabled.
     *
     * @return array[] module configurations keyed by base path
     */
    public static function locateModuleConfigs(): array
    {
        $modules = Yii::$app->cache->get(self::CACHE_ID);

        if ($modules === false || YII_DEBUG) {
            $modules = static::loadModuleConfigs(Yii::$app->params['moduleAutoloadPaths']);
            Yii::$app->cache->set(self::CACHE_ID, $modules);
        }

        return $modules;
    }

    /**
     * Loads module configurations from the given paths by including each module's config.php.
     *
     * Handles duplicate module IDs and path overwrites configured in ModuleManager.
     * Errors in individual config.php files are logged and skipped.
     *
     * @param iterable<string> $paths alias paths to scan
     * @return array[] module configurations keyed by base path
     */
    public static function loadModuleConfigs(iterable $paths): array
    {
        $folders = [];
        foreach ($paths as $path) {
            $dirs = array_filter(
                self::scanPath($path),
                static fn(string $dir) => is_file($dir . DIRECTORY_SEPARATOR . self::CONFIGURATION_FILE),
            );
            $folders = array_merge($folders, $dirs);
        }

        $modules = [];
        $moduleIdFolders = [];
        $preventDuplicates = Yii::$app->moduleManager->preventDuplicatedModules;

        foreach ($folders as $folder) {
            try {
                $moduleConfig = static::readModuleConfig($folder);
                if ($preventDuplicates && isset($moduleIdFolders[$moduleConfig['id']])) {
                    Yii::error(
                        'Duplicated module "' . $moduleConfig['id'] . '" (' . $folder . ') is already loaded from "' . $moduleIdFolders[$moduleConfig['id']] . '"',
                    );
                } else {
                    $modules[$folder] = $moduleConfig;
                    $moduleIdFolders[$moduleConfig['id']] = $folder;
                }
            } catch (\Throwable $e) {
                Yii::error($e);
            }
        }

        if ($preventDuplicates) {
            foreach (Yii::$app->moduleManager->overwriteModuleBasePath as $overwriteId => $overwritePath) {
                if (isset($moduleIdFolders[$overwriteId]) && $moduleIdFolders[$overwriteId] !== $overwritePath) {
                    try {
                        $moduleConfig = static::readModuleConfig($overwritePath);
                        Yii::info('Overwrite path of module "' . $overwriteId . '" to "' . $overwritePath . '"');
                        unset($modules[$moduleIdFolders[$overwriteId]]);
                        $modules[$overwritePath] = $moduleConfig;
                        $moduleIdFolders[$overwriteId] = $overwritePath;
                    } catch (\Throwable $e) {
                        Yii::error($e);
                    }
                }
            }
        }

        return $modules;
    }

    /**
     * Includes and returns the config.php for a module at the given path.
     *
     * @return array the module configuration
     */
    public static function readModuleConfig(string $modulePath): array
    {
        return include $modulePath . DIRECTORY_SEPARATOR . self::CONFIGURATION_FILE;
    }
}
