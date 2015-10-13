<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * Tool for managing modules by command line
 *
 * @package humhub.modules_core.admin.console
 * @since 0.5
 */
class ModuleTool extends HConsoleCommand
{
    /**
     * @throws CException
     */
    public function init()
    {

        Yii::import('application.modules_core.admin.libs.*');
        ModuleManager::flushCache();
        $this->printHeader('Module Tools');
        return parent::init();
    }

    /**
     * @param string $action
     * @param array $params
     * @return bool
     */
    public function beforeAction($action, $params)
    {
        return parent::beforeAction($action, $params);
    }

    /**
     * Lists all installed modules.
     *
     * @param array $args
     */
    public function actionList($args)
    {

        $installedModules = Yii::app()->moduleManager->getInstalledModules();
        ModuleManager::flushCache();

        print "Installed modules: \n\n";

        foreach ($installedModules as $module) {
            print "- [" . $module->getId() . "]\n  " . $module->getName() . " (" . $module->getVersion() . ") " . (($module->isEnabled()) ? "***ENABLED***" : "") . "\n";
            print "  " . $module->getDescription() . "\n\n";
        }
    }

    /**
     * Lists all online available modules.
     *
     * @param array $args
     * @throws CHttpException
     */
    public function actionListOnline($args)
    {

        $onlineModules = new OnlineModuleManager();
        $modules = $onlineModules->getModules();

        print "Online available modules: \n\n";

        foreach ($modules as $module) {
            print "- [" . $module['id'] . "]\n  " . $module['name'] . " (" . $module['latestVersion'] . ") " . ((Yii::app()->moduleManager->isInstalled($module['id'])) ? "***INSTALLED***" : "") . "\n";
            if (isset($module['latestCompatibleVersion']) && $module['latestCompatibleVersion']) {
                if ($module['latestCompatibleVersion'] != $module['latestVersion']) {
                    print "  Latest compatible version:" . $module['latestCompatibleVersion'] . "\n";
                }
            } else {
                print "  *** NO COMPATIBLE VERSION FOUND!";
            }

            print "  " . $module['description'] . "\n\n";
        }
    }

    /**
     * Installs a given module.
     *
     * @param array $args
     * @throws CException
     * @throws CHttpException
     */
    public function actionInstall($args)
    {

        if (!isset($args[0])) {
            print "Error: Module Id required!\n\n";
            print $this->getHelp();
            return;
        }

        $moduleId = $args[0];
        $onlineModules = new OnlineModuleManager();
        $onlineModules->install($moduleId);

        print "\nModule " . $moduleId . " successfully installed!\n";
    }

    /**
     * Uninstalls a given module.
     *
     * @param array $args
     */
    public function actionUninstall($args)
    {

        if (!isset($args[0])) {
            print "Error: Module Id required!\n\n";
            print $this->getHelp();
            return;
        }

        $moduleId = $args[0];
        $module = Yii::app()->moduleManager->getModule($moduleId);

        if ($module == null) {
            print "\nModule " . $moduleId . " is not installed!\n";
            return;
        }

        $module->uninstall($moduleId);

        print "\nModule " . $moduleId . " successfully uninstalled!\n";
    }

    /**
     * Updates a given module to the last available version.
     *
     * @param array $args
     * @param bool $force
     * @throws CHttpException
     */
    public function actionUpdate($args, $force=false)
    {

        if (!isset($args[0])) {
            print "Error: Module Id required!\n\n";
            print $this->getHelp();
            return;
        }

        $moduleId = $args[0];

        
        if (!Yii::app()->moduleManager->isInstalled($moduleId)) {
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
    public function actionUpdateAll($args)
    {
        $installedModules = Yii::app()->moduleManager->getInstalledModules(false, true);
        ModuleManager::flushCache();

        print "Updating modules: \n\n";
        foreach ($installedModules as $moduleId => $moduleClass) {
            $this->actionUpdate(array($moduleId), true);
        }
    }

    /**
     * Returns help and usage information for the module command.
     *
     * @return string
     */
    public function getHelp()
    {
        return <<<EOD
USAGE
  yiic module [action] [parameter]

DESCRIPTION
  This command provides a console interface for manipulating modules. 

EXAMPLES
 * yiic module list
   Lists all installed modules.
        
 * yiic module listonline
   Lists all online available modules.

 * yiic module install moduleId
   Installs a given module.

 * yiic module uninstall moduleId
   Uninstalls a given module.
        
 * yiic module update moduleId
   Updates a given module to the last available version.
        
 * yiic module updateall
   Updates all modules to the latest available version.

EOD;
    }

}
