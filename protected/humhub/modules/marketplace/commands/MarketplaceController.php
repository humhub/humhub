<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\commands;

use humhub\components\console\WithoutModuleAutoload;
use humhub\components\Module;
use humhub\models\ModuleEnabled;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\services\ModuleDiscoveryService;
use humhub\services\ModuleService;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\Json;
use yii\web\HttpException;

/**
 * HumHub Module Management
 *
 * Third-party module configs are not loaded at bootstrap ({@see WithoutModuleAutoload}).
 * Each action that needs a specific module calls {@see loadModule()} to register it on demand.
 *
 * @property \humhub\modules\marketplace\Module $module
 * @since 0.5
 */
#[WithoutModuleAutoload]
class MarketplaceController extends Controller
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $module = Yii::$app->getModule('marketplace');

        if ($module === null || !$module->enabled) {
            $this->error('The marketplace module is disabled by configuration.');
            return false;
        }

        return parent::beforeAction($action);
    }

    /**
     * Lists all installed modules.
     *
     * Reads metadata from module.json — no config.php is loaded.
     */
    public function actionList()
    {
        $installed = ModuleDiscoveryService::findInstalledModules();
        $enabledIds = ModuleEnabled::getEnabledIds();

        $this->heading('Installed Modules (' . count($installed) . ')');

        if (empty($installed)) {
            $this->warn('No modules installed.');
            return 0;
        }

        $this->stdout(sprintf('  %-26s  %-10s  %-12s  %s' . PHP_EOL, 'ID', 'STATUS', 'VERSION', 'NAME'), Console::BOLD);
        $this->stdout('  ' . str_repeat('─', 70) . PHP_EOL);

        foreach ($installed as $moduleId => $info) {
            $isEnabled = in_array($moduleId, $enabledIds);
            $name = $this->readModuleName($info['basePath'], $moduleId);
            $this->stdout(sprintf('  %-26s  ', $moduleId));
            $this->stdout(sprintf('%-10s', $isEnabled ? 'enabled' : 'disabled'), $isEnabled ? Console::FG_GREEN : Console::FG_YELLOW);
            $this->stdout(sprintf('  %-12s  %s' . PHP_EOL, $info['version'] ?? '-', $name));
        }

        $enabledCount = count(array_intersect($enabledIds, array_keys($installed)));
        $this->stdout(PHP_EOL . '  ' . $enabledCount . ' of ' . count($installed) . ' module(s) enabled.' . PHP_EOL);

        return 0;
    }

    /**
     * Lists all online available modules.
     *
     * Uses filesystem metadata to determine installed status — no config.php is loaded.
     */
    public function actionListOnline()
    {
        $this->stdout(PHP_EOL . 'Fetching online module list...' . PHP_EOL);

        $modules = $this->getMarketplaceModule()->onlineModuleManager->getModules();
        $installed = ModuleDiscoveryService::findInstalledModules();

        $this->heading('Online Modules (' . count($modules) . ')');

        if (empty($modules)) {
            $this->warn('No modules found.');
            return 0;
        }

        $this->stdout(
            sprintf('  %-26s  %-12s  %-12s  %-14s  %s' . PHP_EOL, 'ID', 'INSTALLED', 'LATEST', 'COMPATIBLE', 'NAME'),
            Console::BOLD,
        );
        $this->stdout('  ' . str_repeat('─', 82) . PHP_EOL);

        foreach ($modules as $module) {
            $isInstalled = array_key_exists($module['id'], $installed);
            $installedVersion = $installed[$module['id']]['version'] ?? null;
            $compat = $module['latestCompatibleVersion'] ?? '-';

            $this->stdout(sprintf('  %-26s  ', $module['id']));
            if ($isInstalled) {
                $this->stdout(sprintf('%-12s', $installedVersion), Console::FG_GREEN);
            } else {
                $this->stdout(sprintf('%-12s', '-'), Console::FG_YELLOW);
            }
            $this->stdout(sprintf('  %-12s  %-14s  %s' . PHP_EOL, $module['latestVersion'] ?? '-', $compat, $module['name']));
        }

        return 0;
    }

    /**
     * Installs a given module.
     *
     * @throws ErrorException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws HttpException
     */
    public function actionInstall(string $moduleId)
    {
        $installed = ModuleDiscoveryService::findInstalledModules();

        if (array_key_exists($moduleId, $installed)) {
            $this->warn('Module ' . $moduleId . ' is already installed (v' . ($installed[$moduleId]['version'] ?? '?') . ').');
            return 0;
        }

        $this->stdout(PHP_EOL . 'Installing ' . $moduleId . '...' . PHP_EOL);

        $this->getMarketplaceModule()->onlineModuleManager->install($moduleId);

        $version = ModuleDiscoveryService::findInstalledVersion($moduleId);
        $this->success('Module ' . $moduleId . ($version ? ' (v' . $version . ')' : '') . ' installed successfully.');

        return 0;
    }

    /**
     * Uninstalls a given module.
     *
     * @throws ErrorException
     * @throws Exception
     */
    public function actionRemove(string $moduleId)
    {
        $module = $this->loadModule($moduleId);

        if ($module === null) {
            $this->error('Module ' . $moduleId . ' is not installed.');
            return 1;
        }

        $this->stdout(PHP_EOL . 'Removing ' . $moduleId . '...' . PHP_EOL);

        (new ModuleService($module))->remove();

        $this->success('Module ' . $moduleId . ' removed successfully.');

        return 0;
    }

    /**
     * Updates a module to the latest compatible version.
     *
     * @throws Exception
     */
    public function actionUpdate(string $moduleId)
    {
        $installed = ModuleDiscoveryService::findInstalledModules();

        if (!array_key_exists($moduleId, $installed)) {
            $this->error('Module ' . $moduleId . ' is not installed.');
            return 1;
        }

        $moduleInfo = $this->getMarketplaceModule()->onlineModuleManager->getModuleInfo($moduleId);

        if (!isset($moduleInfo['latestCompatibleVersion'])) {
            $this->warn('No compatible version for ' . $moduleId . ' found online.');
            return 0;
        }

        $currentVersion = $installed[$moduleId]['version'] ?? '?';
        $newVersion = $moduleInfo['latestCompatibleVersion']['version'];

        if ($newVersion === $currentVersion) {
            $this->warn('Module ' . $moduleId . ' is already up to date (v' . $currentVersion . ').');
            return 0;
        }

        $this->stdout(PHP_EOL . 'Updating ' . $moduleId . ' (v' . $currentVersion . ' → v' . $newVersion . ')...' . PHP_EOL);

        // No pre-loading: OnlineModuleManager::update() removes old files first, then
        // calls install() which registers the fresh config.php. Loading the old module
        // here would put stale class definitions into the process before the files change.
        $this->getMarketplaceModule()->onlineModuleManager->update($moduleId);

        $this->success('Module ' . $moduleId . ' updated to v' . $newVersion . '.');

        return 0;
    }

    /**
     * Updates all modules to the latest available version.
     */
    public function actionUpdateAll()
    {
        $onlineModuleManager = $this->getMarketplaceModule()->onlineModuleManager;

        $this->stdout(PHP_EOL . 'Checking for module updates...' . PHP_EOL);

        $updates = $onlineModuleManager->getModuleUpdates();

        if (empty($updates)) {
            $this->success('All modules are up to date.');
        } else {
            $this->stdout('Found ' . count($updates) . ' module(s) with available updates.' . PHP_EOL);

            $succeeded = 0;
            foreach ($updates as $moduleId => $info) {
                $this->stdout(PHP_EOL . '── ' . $moduleId . ' ──' . PHP_EOL, Console::BOLD);
                try {
                    $this->actionUpdate($moduleId);
                    $succeeded++;
                } catch (\Throwable $e) {
                    $this->error('Update of ' . $moduleId . ' failed: ' . $e->getMessage());
                }
            }

            $this->stdout(PHP_EOL);
            if ($succeeded === count($updates)) {
                $this->success('Updated ' . $succeeded . ' of ' . count($updates) . ' module(s).');
            } else {
                $this->warn('Updated ' . $succeeded . ' of ' . count($updates) . ' module(s). ' . (count($updates) - $succeeded) . ' failed.');
            }
        }

        // Reinstall modules that are marked as enabled in the DB but missing on disk.
        $installed = ModuleDiscoveryService::findInstalledModules();
        $missing = array_diff(ModuleEnabled::getEnabledIds(), array_keys($installed));

        if (!empty($missing)) {
            $this->stdout(PHP_EOL . 'Reinstalling ' . count($missing) . ' missing module(s)...' . PHP_EOL);

            foreach ($missing as $moduleId) {
                try {
                    $onlineModuleManager->install($moduleId);
                    $reinstalledModule = Yii::$app->moduleManager->getModule($moduleId);
                    if ($reinstalledModule !== null) {
                        $reinstalledModule->update();
                    }
                    $this->success('Reinstalled ' . $moduleId . '.');
                } catch (\Exception $e) {
                    $this->error('Could not reinstall ' . $moduleId . ': ' . $e->getMessage());
                }
            }
        }

        return 0;
    }

    /**
     * Enables an installed module.
     *
     * @param string $moduleId the module id
     * @return int the exit code
     */
    public function actionEnable(string $moduleId)
    {
        $this->stdout(PHP_EOL . 'Enabling ' . $moduleId . '...' . PHP_EOL);

        $module = $this->loadModule($moduleId);
        if ($module === null) {
            $this->error('Module ' . $moduleId . ' is not installed.');
            return 1;
        }

        if (ModuleEnabled::findOne(['module_id' => $moduleId])) {
            $this->warn('Module ' . $moduleId . ' is already enabled.');
            return 0;
        }

        $module->enable();

        $this->success('Module ' . $moduleId . ' enabled.');
        return 0;
    }

    /**
     * Disables an enabled module.
     *
     * @param string $moduleId the module id
     * @return int the exit code
     */
    public function actionDisable(string $moduleId)
    {
        $module = $this->loadModule($moduleId);
        if ($module === null) {
            $this->error('Module ' . $moduleId . ' is not installed.');
            return 1;
        }

        if (!ModuleEnabled::findOne(['module_id' => $moduleId])) {
            $this->warn('Module ' . $moduleId . ' is not enabled.');
            return 0;
        }

        if (!$this->confirm(
            Yii::t('MarketplaceModule.base', 'All {moduleId} module content will be deleted. Continue?', ['moduleId' => $moduleId]),
            false,
        )) {
            return 1;
        }

        $this->stdout(PHP_EOL . 'Disabling ' . $moduleId . '...' . PHP_EOL);

        $module->disable();

        $this->success('Module ' . $moduleId . ' disabled.');
        return 0;
    }

    /**
     * Registers a license key for a paid module.
     *
     * @param string $licenceKey
     * @throws ErrorException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws HttpException
     */
    public function actionRegister(string $licenceKey)
    {
        if (empty($licenceKey)) {
            $this->error('License key cannot be empty.');
            return 1;
        }

        $result = HumHubAPI::request('v1/modules/registerPaid', ['licenceKey' => $licenceKey]);

        if (!isset($result['status'])) {
            $this->error('Could not connect to the HumHub API.');
            return 1;
        }

        if ($result['status'] !== 'ok' && $result['status'] !== 'created') {
            $this->error('Invalid license key.');
            return 1;
        }

        $this->success('License key registered successfully.');
        return 0;
    }

    /**
     * Displays detailed information about a module.
     *
     * @param string $moduleId Module ID
     */
    public function actionInfo(string $moduleId)
    {
        $installed = ModuleDiscoveryService::findInstalledModules();
        $isInstalled = array_key_exists($moduleId, $installed);
        $isEnabled = $isInstalled && ModuleEnabled::findOne(['module_id' => $moduleId]) !== null;

        $module = $isInstalled ? $this->loadModule($moduleId) : null;

        $this->heading('Module: ' . $moduleId);

        $this->printInfoRow('Installed', $isInstalled ? 'Yes' : 'No', $isInstalled ? Console::FG_GREEN : Console::FG_RED);

        if (!$isInstalled) {
            $this->stdout(PHP_EOL);
            return 1;
        }

        $this->printInfoRow('Enabled', $isEnabled ? 'Yes' : 'No', $isEnabled ? Console::FG_GREEN : Console::FG_YELLOW);

        if ($module !== null) {
            $this->printInfoRow('Name', $module->name);
            $this->printInfoRow('Version', $module->version);
            $this->printInfoRow('Description', $module->description);
            $this->printInfoRow('Path', $module->basePath);
        } else {
            $this->printInfoRow('Version', $installed[$moduleId]['version'] ?? '-');
            $this->printInfoRow('Path', $installed[$moduleId]['basePath']);
            $this->stdout(PHP_EOL);
            $this->warn('Module config could not be loaded (broken config.php).');
        }

        $this->stdout(PHP_EOL);
        return 0;
    }

    /**
     * Explicitly loads a single module's config.php and registers it with the module manager.
     *
     * Returns the Module instance on success, or null if the module is not found on disk
     * or its config.php throws an error.
     */
    private function loadModule(string $moduleId): ?Module
    {
        $basePath = ModuleDiscoveryService::findModuleBasePath($moduleId);
        if ($basePath === null) {
            return null;
        }

        try {
            Yii::$app->moduleManager->register($basePath);
        } catch (\Throwable $e) {
            Yii::error('Could not load config for module "' . $moduleId . '": ' . $e->getMessage());
            return null;
        }

        return Yii::$app->moduleManager->getModule($moduleId, false);
    }

    private function heading(string $title): void
    {
        $this->stdout(PHP_EOL . $title . PHP_EOL, Console::BOLD);
        $this->stdout(str_repeat('─', max(mb_strlen($title) + 4, 44)) . PHP_EOL);
    }

    private function success(string $message): void
    {
        $this->stdout('✓ ', Console::FG_GREEN, Console::BOLD);
        $this->stdout($message . PHP_EOL);
    }

    private function error(string $message): void
    {
        $this->stdout('✗ ', Console::FG_RED, Console::BOLD);
        $this->stdout($message . PHP_EOL);
    }

    private function warn(string $message): void
    {
        $this->stdout('! ', Console::FG_YELLOW, Console::BOLD);
        $this->stdout($message . PHP_EOL);
    }

    private function printInfoRow(string $label, string $value, ?int $valueColor = null): void
    {
        $this->stdout(sprintf('  %-14s', $label . ':'));
        if ($valueColor !== null) {
            $this->stdout($value . PHP_EOL, $valueColor, Console::BOLD);
        } else {
            $this->stdout($value . PHP_EOL);
        }
    }

    private function readModuleName(string $basePath, string $fallback): string
    {
        $path = $basePath . DIRECTORY_SEPARATOR . 'module.json';
        if (!is_file($path)) {
            return $fallback;
        }

        try {
            return Json::decode(file_get_contents($path))['name'] ?? $fallback;
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function getMarketplaceModule(): \humhub\modules\marketplace\Module
    {
        return Yii::$app->getModule('marketplace');
    }
}
