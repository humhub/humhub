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
 * Module Manager
 *
 *  - Starts module autostart files
 *  - Handles enabled modules
 *  - Modules autostarts.php registers to it for events & co
 *
 */
class ModuleManager extends CApplicationComponent
{

    const AUTOSTART_CACHE_FILE_NAME = "cache_autostart.php";

    /**
     * List of all enabled module ids
     *
     * @var Array
     */
    private $enabledModules = array();

    /**
     * Array of installed modules populated on autostart.php register
     *
     * @var Array moduleId => moduleClass
     */
    private $installedModules = array();

    /**
     * Initializes the module manager
     */
    public function init()
    {

        parent::init();

        if (Yii::app()->params['installed']) {

            // Load all enabled modules
            $cacheId = "enabledModules";
            $cacheValue = Yii::app()->cache->get($cacheId);

            if ($cacheValue === false || !is_array($cacheValue)) {

                foreach (ModuleEnabled::model()->findAll() as $em) {
                    $this->enabledModules[] = $em->module_id;
                }

                Yii::app()->cache->set($cacheId, $this->enabledModules, HSetting::Get('expireTime', 'cache'));
            } else {
                $this->enabledModules = $cacheValue;
            }
        }

        // Intercept this controller
        Yii::app()->interceptor->intercept($this);
    }

    /**
     * Starts module manager which executes all enabled autoloaders
     */
    public function start()
    {
        $cacheEnabled = (get_class(Yii::app()->cache) != 'CDummyCache');
        $cacheFileName = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . self::AUTOSTART_CACHE_FILE_NAME;

        // Fastlane, when cache enabled and cachefile exists
        if ($cacheEnabled && file_exists($cacheFileName)) {
            require_once($cacheFileName);
            return;
        }

        $autostartFiles = array();

        /*
          // Recursively collect all module_core autostarts
          $modulesCorePath = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'modules_core';
          $modules = scandir($modulesCorePath);
          foreach ($modules as $moduleId) {
          $autostartFiles[] = $modulesCorePath . DIRECTORY_SEPARATOR . $moduleId . DIRECTORY_SEPARATOR . 'autostart.php';
          }

          // Collect autostarts of enabled modules
          $modulesCustomPath = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'modules';
          foreach ($this->enabledModules as $moduleId) {
          $autostartFiles[] = $modulesCustomPath . DIRECTORY_SEPARATOR . $moduleId . DIRECTORY_SEPARATOR . 'autostart.php';
          }
         */

        // Recursively collect all moodules / modules_core autostarts
        $modulesPaths = array(Yii::app()->getModulePath(), Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'modules_core');
        foreach ($modulesPaths as $modulePath) {
            $modules = scandir($modulePath);
            foreach ($modules as $moduleId) {
                if (is_dir($modulePath . DIRECTORY_SEPARATOR . $moduleId)) {
                    $autostartFiles[] = $modulePath . DIRECTORY_SEPARATOR . $moduleId . DIRECTORY_SEPARATOR . 'autostart.php';
                }
            }
        }

        // Execute (and cache) found autostarts
        $cacheFileContent = "";
        foreach ($autostartFiles as $autoloadFile) {
            if (is_file($autoloadFile)) {

                require_once($autoloadFile);

                // Cache content of autostart file
                if ($cacheEnabled) {
                    $cacheFileContent .= file_get_contents($autoloadFile);
                }
            }
        }

        if ($cacheEnabled) {
            file_put_contents($cacheFileName, $cacheFileContent);
        }
    }

    /**
     * Flushes Module Managers Cache
     */
    public static function flushCache()
    {

        // Delete Autoloader Cache File
        $cacheFileName = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . self::AUTOSTART_CACHE_FILE_NAME;
        if (file_exists($cacheFileName)) {
            unlink($cacheFileName);
        }

        // Delete Enabled Modules List
        $cacheId = "enabledModules";
        Yii::app()->cache->delete($cacheId);
    }

    /**
     * Registers a module
     * This is usally called in the autostart file of the module.
     *
     * - id
     * - class          Module Base Class
     * - import         Global Module Imports
     * - events         Events to catch
     *
     * - isCoreModule   Core Modules only
     *
     * @param Array $definition
     */
    public function register($definition)
    {

        if (!isset($definition['class']) || !isset($definition['id'])) {
            throw new Exception("Register Module needs module Id and Class!");
        }

        $isCoreModule = (isset($definition['isCoreModule']) && $definition['isCoreModule']);

        $this->installedModules[$definition['id']] = $definition['class'];

        // Not enabled and no core module
        if (!$isCoreModule && !in_array($definition['id'], $this->enabledModules)) {
            return;
        }

        // Handle Submodules
        if (!isset($definition['modules'])) {
            $definition['modules'] = array();
        }
        
        // Append URL Rules
        if (isset($definition['urlManagerRules'])) {
            Yii::app()->urlManager->addRules($definition['urlManagerRules'], false);
        }
         
        
        // Register Yii Module
        Yii::app()->setModules(array(
            $definition['id'] => array(
                'class' => $definition['class'],
                'modules' => $definition['modules']
            ),
        ));

        // Set Imports
        if (isset($definition['import'])) {
            Yii::app()->setImport($definition['import']);
        }

        // Register Event Handlers
        if (isset($definition['events'])) {
            foreach ($definition['events'] as $event) {
                Yii::app()->interceptor->preattachEventHandler(
                        $event['class'], $event['event'], $event['callback']
                );
            }
        }
        
    }

    /**
     * Returns Module Base Class of installed module neither when not enabled.
     *
     * @param String $id Module Id
     * @return HWebModule
     */
    public function getModule($id)
    {

        try {
            // When enabled, returned it directly
            if (Yii::app()->getModule($id) != null) {
                return Yii::app()->getModule($id);
            }

            // Not enabled, but installed - create it
            if (isset($this->installedModules[$id])) {
                $class = $this->installedModules[$id];
                return Yii::createComponent($class, $id, null);
            }
        } catch (Exception $ex) {
            Yii::log("Loading of module " . $id . " failed! " . $ex->getMessage(), 'error');
        }

        return null;
    }

    /**
     * Returns a list of all installed modules
     *
     * @param boolean $includeCoreModules include also core modules
     * @param boolean $returnClassName instead of instance
     * @return Array of installed Modules
     */
    public function getInstalledModules($includeCoreModules = false, $returnClassName = false)
    {

        $installed = array();
        foreach ($this->installedModules as $moduleId => $className) {

            if (!$includeCoreModules && strpos($className, 'application.modules_core') !== false) {
                continue;
            }

            if ($returnClassName) {
                $installed[$moduleId] = $className;
            } else {

                try {
                    $module = $this->getModule($moduleId);
                    if ($module != null) {
                        $installed[$moduleId] = $module;
                    }
                } catch (Exception $ex) {
                    Yii::log('Could not instanciate module: ' . $moduleId . "." . $ex->getMessage(), 'error');
                    self::flushCache();
                }
            }
        }

        return $installed;
    }

    /**
     * Returns a list of all enabled modules
     */
    public function getEnabledModules()
    {

        $modules = array();
        foreach ($this->enabledModules as $moduleId) {
            $module = $this->getModule($moduleId);
            if ($module != null) {
                $modules[] = $module;
            }
        }

        return $modules;
    }

    /**
     * Checks if a module is enabled.
     *
     * @param String $moduleId
     * @return boolean
     */
    public function isEnabled($moduleId)
    {
        return (in_array($moduleId, $this->enabledModules));
    }

    /**
     * Checks if a module id is installed.
     *
     * @param String $moduleId
     * @return boolean
     */
    public function isInstalled($moduleId)
    {
        return (array_key_exists($moduleId, $this->installedModules));
    }

    /**
     * Checks if a given module Id can uninstalled
     */
    public function canUninstall($moduleId)
    {

        if ($this->isInstalled($moduleId)) {

            $module = $this->getModule($moduleId);

            if ($module == null) {
                throw new CException(Yii::t('base', 'Could not find requested module!'));
            }

            if ($module->isCoreModule) {
                return false;
            }
        }

        return true;
    }

    /**
     * Removes module folder
     * 
     * @param String $moduleId
     */
    public function removeModuleFolder($moduleId)
    {

        $modulePath = Yii::app()->getModulePath() . DIRECTORY_SEPARATOR . $moduleId;


        $moduleBackupFolder = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'module_backups';
        if (!is_dir($moduleBackupFolder)) {
            if (!@mkdir($moduleBackupFolder)) {
                throw new CException("Could not create module backup folder!");
            }
        }

        $backupFolderName = $moduleBackupFolder . DIRECTORY_SEPARATOR . $moduleId . "_" . time();
        if (!@rename($modulePath, $backupFolderName)) {
            throw new CException("Could not remove module folder!");
        }
    }

}

?>
