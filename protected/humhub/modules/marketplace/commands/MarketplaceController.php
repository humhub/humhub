<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\commands;

use humhub\components\Module;
use humhub\models\ModuleEnabled;
use humhub\modules\admin\libs\HumHubAPI;
use Yii;
use yii\base\InvalidArgumentException;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * HumHub Module Managament
 *
 * @property \humhub\modules\marketplace\Module $module
 * @since 0.5
 */
class MarketplaceController extends Controller
{

    /**
     * @inerhitdoc
     */
    public function beforeAction($action)
    {
        /** @var \humhub\modules\marketplace\Module $module */
        $module = Yii::$app->getModule('marketplace');

        if ($module === null || !$module->enabled) {
            print "Fatal: The module marketplace is disabled by configuration!\n\n";
            return false;
        }

        return parent::beforeAction($action);
    }


    /**
     * Lists all installed and enabled modules.
     *
     * @throws \yii\base\Exception
     */
    public function actionList()
    {
        Yii::$app->moduleManager->flushCache();
        $installedModules = Yii::$app->moduleManager->getModules();

        print "Installed modules: \n\n";

        $mask = "| %-20s | %10s |%20s | %-30s \n";
        printf($mask, 'ID', 'ENABLED', 'INSTALLED VERSION', 'TITLE');
        foreach ($installedModules as $module) {
            printf($mask, $module->id, (Yii::$app->hasModule($module->id) ? 'Yes' : 'No'), $module->getVersion(), $module->getName());
        }
    }

    /**
     * Lists all online available modules.
     */
    public function actionListOnline()
    {
        $modules = $this->getMarketplaceModule()->onlineModuleManager->getModules();

        print "Online available modules: \n\n";

        $mask = "| %-20s | %9s | %14s | %21s | %-30s \n";
        printf($mask, 'ID', 'INSTALLED', 'LATEST VERSION', 'LATEST COMPAT VERSION', 'TITLE');

        foreach ($modules as $module) {

            printf($mask, $module['id'], (Yii::$app->moduleManager->hasModule($module['id']) ? 'Yes' : 'No'), $module['latestVersion'], (isset($module['latestCompatibleVersion']) && $module['latestCompatibleVersion']) ? $module['latestCompatibleVersion'] : "-", $module['name']);
        }
    }

    /**
     * Installs a given module.
     *
     * @param string $moduleId
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\HttpException
     */
    public function actionInstall($moduleId)
    {
        $this->getMarketplaceModule()->onlineModuleManager->install($moduleId);

        print "\nModule " . $moduleId . " successfully installed!\n";
    }

    /**
     * Uninstalls a given module.
     *
     * @param string $moduleId
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    public function actionRemove($moduleId)
    {

        $module = Yii::$app->moduleManager->getModule($moduleId);

        if ($module == null) {
            print "\nModule " . $moduleId . " is not installed!\n";
            return;
        }

        Yii::$app->moduleManager->removeModule($module->id);

        print "\nModule " . $moduleId . " successfully uninstalled!\n";
    }

    /**
     * Updates a module
     *
     * @param string $moduleId
     * @throws \yii\base\Exception
     * @todo Handle no marketplace modules
     *
     */
    public function actionUpdate($moduleId)
    {

        if (!Yii::$app->moduleManager->hasModule($moduleId)) {
            print "\nModule " . $moduleId . " is not installed!\n";
            exit;
        }

        // Look online for module
        $moduleInfo = $this->getMarketplaceModule()->onlineModuleManager->getModuleInfo($moduleId);

        if (!isset($moduleInfo['latestCompatibleVersion'])) {
            print "No compatible version for " . $moduleId . " found online!\n";
            return;
        }

        $module = Yii::$app->moduleManager->getModule($moduleId);

        if ($moduleInfo['latestCompatibleVersion']['version'] == $module->getVersion()) {
            print "Module " . $moduleId . " already up to date!\n";
            return;
        }

        $this->getMarketplaceModule()->onlineModuleManager->update($moduleId);

        print "Module " . $moduleId . " successfully updated!\n";
    }

    /**
     * Updates all modules to the latest available version.
     */
    public function actionUpdateAll()
    {
        $onlineModuleManager = $this->getMarketplaceModule()->onlineModuleManager;

        $i = 0;
        foreach ($onlineModuleManager->getModuleUpdates() as $moduleId => $info) {
            try {
                $this->actionUpdate($moduleId);
            } catch (InvalidArgumentException $ex) {
                print "Module " . $moduleId . " - Error: " . $ex->getMessage() . "\n";
            } catch (\Exception $ex) {
                print "Module " . $moduleId . " - Error: " . $ex->getMessage() . "\n";
            }
            $i++;
        }

        print "\nUpdated " . $i . " outdated modules. \n";

        /**
         * Looking up modules which are marked as installed but not loaded.
         * Try to get recent version online.
         */
        // Also install modules which are seems to be installed
        $installedModules = Yii::$app->moduleManager->getModules(['returnClass' => true]);

        foreach (ModuleEnabled::getEnabledIds() as $moduleId) {
            if (!in_array($moduleId, array_keys($installedModules))) {
                // Module seems to be installed - but cannot be loaded
                // Try force re-install
                try {
                    $onlineModuleManager->install($moduleId);
                    print "Reinstalled: " . $moduleId . "\n";
                } catch (\Exception $ex) {

                }
            }
        }
    }

    /**
     * Enables an installed module
     *
     * @param string $moduleId the module id
     * @return int the exit code
     */
    public function actionEnable($moduleId)
    {
        $this->stdout(Yii::t('MarketplaceModule.base', "--- Enable module: {moduleId} ---\n\n", ['moduleId' => $moduleId]), Console::BOLD);

        /** @var Module $module */
        $module = Yii::$app->moduleManager->getModule($moduleId);
        if ($module === null) {
            $this->stdout(Yii::t('MarketplaceModule.base', "Module not found!\n"), Console::FG_RED, Console::BOLD);
            return 1;
        }

        $module->enable();

        $this->stdout(Yii::t('MarketplaceModule.base', "\nModule successfully enabled!\n"), Console::FG_GREEN, Console::BOLD);
        return 0;
    }

    /**
     * Disables an enabled module
     *
     * @param string $moduleId the module id
     * @return int the exit code
     */
    public function actionDisable($moduleId)
    {
        if (!$this->confirm(Yii::t('MarketplaceModule.base', 'All {moduleId} module content will be deleted. Continue?', ['moduleId' => $moduleId]), false)) {
            return 1;
        }

        $this->stdout(Yii::t('MarketplaceModule.base', "--- Disable module: {moduleId} ---\n\n", ['moduleId' => $moduleId]), Console::BOLD);

        /** @var Module $module */
        $module = Yii::$app->moduleManager->getModule($moduleId);
        if ($module === null || !Yii::$app->hasModule($moduleId)) {
            $this->stdout(Yii::t('MarketplaceModule.base', "Module not found or activated!\n"), Console::FG_RED, Console::BOLD);
            return 1;
        }

        $module->disable();

        $this->stdout(Yii::t('MarketplaceModule.base', "\nModule successfully disabled!\n"), Console::FG_GREEN, Console::BOLD);
        return 0;
    }

    /**
     * Registers a given module.
     *
     * @param string $licenceKey
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\HttpException
     */
    public function actionRegister($licenceKey)
    {
        if (empty($licenceKey)) {
            $this->stdout(Yii::t('MarketplaceModule.base', 'Module licence key cannot be empty!' . "\n"), Console::FG_RED, Console::BOLD);
            return 1;
        }

        $result = HumHubAPI::request('v1/modules/registerPaid', ['licenceKey' => $licenceKey]);

        if (!isset($result['status'])) {
            $this->stdout(Yii::t('MarketplaceModule.base', 'Could not connect to HumHub API!' . "\n"), Console::FG_RED, Console::BOLD);
            return 1;
        }

        if ($result['status'] != 'ok' && $result['status'] != 'created') {
            $this->stdout(Yii::t('MarketplaceModule.base', 'Invalid module licence key!' . "\n"), Console::FG_RED, Console::BOLD);
            return 1;
        }

        $this->stdout(Yii::t('MarketplaceModule.base', 'Module licence added!' . "\n"), Console::FG_GREEN, Console::BOLD);
        return 0;
    }

    /**
     * @return \humhub\modules\marketplace\Module
     */
    private function getMarketplaceModule()
    {
        return Yii::$app->getModule('marketplace');
    }

}
