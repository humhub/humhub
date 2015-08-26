<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\commands;

use Yii;
use humhub\modules\admin\libs\OnlineModuleManager;

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

            printf($mask, $module['id'], (Yii::$app->moduleManager->hasModule($module['id']) ? 'Yes' : 'No'), $module['latestVersion'], (isset($module['latestCompatibleVersion']) && $module['latestCompatibleVersion']) ? $module['latestCompatibleVersion'] : "-", $module['name']
            );
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
     * Updates a given module to the last available version.
     *
     * @param array $args
     * @param bool $force
     * @throws CHttpException
     */
    public function actionUpdate($args, $force = false)
    {

        if (!isset($args[0])) {
            print "Error: Module Id required!\n\n";
            print $this->getHelp();
            return;
        }

        $moduleId = $args[0];


        if (!Yii::app()->moduleManager->hasModule($moduleId)) {
            print "\nModule " . $moduleId . " is not installed!\n";
            return;
        }

        // Look online for module
        $onlineModules = new OnlineModuleManager();
        $moduleInfo = $onlineModules->getModuleInfo($moduleId);

        if (!isset($moduleInfo['latestCompatibleVersion'])) {
            print "No compatible version for " . $moduleId . " found online!\n";
            return;
        }

        if (!$force) {
            $module = Yii::app()->moduleManager->getModule($moduleId);

            if ($moduleInfo['latestCompatibleVersion']['version'] == $module->getVersion()) {
                print "Module " . $moduleId . " already up to date!\n";
                return;
            }
        }

        $onlineModules->update($moduleId);

        print "Module " . $moduleId . " successfully updated!\n";
    }

    /**
     * Updates all modules to the latest available version.
     *
     * @param array $args
     */
    public function actionUpdateAll()
    {
        return;
        
        $installedModules = Yii::app()->moduleManager->getInstalledModules(false, true);
        ModuleManager::flushCache();

        print "Updating modules: \n\n";
        foreach ($installedModules as $moduleId => $moduleClass) {
            $this->actionUpdate(array($moduleId), true);
        }
        
    }

}
