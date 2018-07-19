<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\commands;

use humhub\components\Module;
use humhub\modules\admin\libs\OnlineModuleManager;
use Yii;
use yii\helpers\Console;

/**
 * HumHub Module Managament
 *
 * @package humhub.modules_core.admin.console
 * @since 0.5
 */
class ModuleController extends \yii\console\Controller
{

    /**
     * Lists all installed and enabled modules.
     *
     * @param array $args
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
     *
     * @param array $args
     * @throws CHttpException
     */
    public function actionListOnline()
    {
        $onlineModules = new OnlineModuleManager();
        $modules = $onlineModules->getModules();

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
     * @throws CHttpException
     */
    public function actionInstall($moduleId)
    {
        $onlineModules = new OnlineModuleManager();
        $onlineModules->install($moduleId);

        print "\nModule " . $moduleId . " successfully installed!\n";
    }

    /**
     * Uninstalls a given module.
     *
     * @param string $moduleId
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
     * @todo Handle no marketplace modules
     *
     * @param string $moduleId
     */
    public function actionUpdate($moduleId)
    {

        if (!Yii::$app->moduleManager->hasModule($moduleId)) {
            print "\nModule " . $moduleId . " is not installed!\n";
            exit;
        }

        // Look online for module
        $onlineModules = new OnlineModuleManager();
        $moduleInfo = $onlineModules->getModuleInfo($moduleId);

        if (!isset($moduleInfo['latestCompatibleVersion'])) {
            print "No compatible version for " . $moduleId . " found online!\n";
            return;
        }

        $module = Yii::$app->moduleManager->getModule($moduleId);

        if ($moduleInfo['latestCompatibleVersion']['version'] == $module->getVersion()) {
            print "Module " . $moduleId . " already up to date!\n";
            return;
        }

        $onlineModules->update($moduleId);

        print "Module " . $moduleId . " successfully updated!\n";
    }

    /**
     * Updates all modules to the latest available version.
     */
    public function actionUpdateAll()
    {
        // Also install modules which are seems to be installed
        $installedModules = Yii::$app->moduleManager->getModules(['returnClass' => true]);

        foreach ($installedModules as $moduleId => $className) {
            try {
                $this->actionUpdate($moduleId);
            } catch (\yii\base\InvalidArgumentException $ex) {
                print "Module " . $moduleId . " - Error: " . $ex->getMessage() . "\n";
            } catch (\Exception $ex) {
                print "Module " . $moduleId . " - Error: " . $ex->getMessage() . "\n";
            }
        }

        /**
         * Looking up modules which are marked as installed but not loaded.
         * Try to get recent version online.
         */
        foreach (\humhub\models\ModuleEnabled::getEnabledIds() as $moduleId) {
            if (!in_array($moduleId, array_keys($installedModules))) {
                // Module seems to be installed - but cannot be loaded
                // Try force re-install
                try {
                    $onlineModules = new OnlineModuleManager();
                    $onlineModules->install($moduleId);
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
        $this->stdout(Yii::t('AdminModule.console', "--- Enable module: {moduleId} ---\n\n", ['moduleId' => $moduleId]), Console::BOLD);

        /** @var Module $module */
        $module = Yii::$app->moduleManager->getModule($moduleId);
        if ($module === null) {
            $this->stdout(Yii::t('AdminModule.console', "Module not found!\n"), Console::FG_RED, Console::BOLD);
            return 1;
        }

        $module->enable();

        $this->stdout(Yii::t('AdminModule.console', "\nModule successfully enabled!\n"), Console::FG_GREEN, Console::BOLD);
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
        $this->stdout(Yii::t('AdminModule.console', "--- Disable module: {moduleId} ---\n\n", ['moduleId' => $moduleId]), Console::BOLD);

        /** @var Module $module */
        $module = Yii::$app->moduleManager->getModule($moduleId);
        if ($module === null || !Yii::$app->hasModule($moduleId)) {
            $this->stdout(Yii::t('AdminModule.console', "Module not found or activated!\n"), Console::FG_RED, Console::BOLD);
            return 1;
        }

        $module->disable();

        $this->stdout(Yii::t('AdminModule.console', "\nModule successfully disabled!\n"), Console::FG_GREEN, Console::BOLD);
        return 0;
    }

}
