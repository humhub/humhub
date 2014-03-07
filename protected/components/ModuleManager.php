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
 * ModuleManager allows dynamic enabling/disabling of application modules.
 *
 * Each module has a autostart.php which can register the module.
 *
 * Modules must register with a module definition, which holds all relevant
 * information about it.
 *
 *      Module Definition Array:
 *           id => mymodule                         (also folder name under /modules/...)
 *           title => My Module
 *           icon => cssClass
 *           description => someText                (For Admin Manage Modules)
 *           isSpaceModule => true/FALSE        (Is a workspace module)
 *           isCoreModule => true/FALSE             (Is core module, always enabled)
 *           configRoute => 'mymodule/configure'    (Configuration URL for SuperAdmin)
 *
 * @todo cache enabled modules - problem module manager started before caching
 *
 * @package humhub.components
 * @since 0.5
 */
class ModuleManager extends CApplicationComponent {

    const AUTOSTART_CACHE_FILE_NAME = "cache_autostart.php";

    /**
     * @var Array of all registered module definitions
     */
    public $registeredModules;

    /**
     * @var Array of enabled module ids.
     */
    public $enabledModules;

    /**
     * @var Array of registered content model classes.
     */
    public $registeredContentModels = array();

    /**
     * Initializes the application component.
     * This should also should check which module is enabled
     */
    public function init() {

        parent::init();

        if (Yii::app()->params['installed'])
            $this->loadEnabledModules();

        // Intercept this controller
        Yii::app()->interceptor->intercept($this);
    }

    public function start() {

        $this->executeAutoloaders();
        #print "start";
        #die();
    }

    /**
     * Searches and executes all module autoloaders.
     *
     * The module autoloaders are stored in a file "autostart.php" which can be
     * placed in the root directory of the module.
     *
     * @todo Caching autostarts
     * @todo Remove rendundant code
     */
    private function executeAutoloaders() {

        $cacheEnabled = (get_class(Yii::app()->cache) != 'CDummyCache');

        $cacheFileName = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . self::AUTOSTART_CACHE_FILE_NAME;
        if ($cacheEnabled && file_exists($cacheFileName)) {
            require_once($cacheFileName);
            return;
        }

        $fileNames = array();

        // Looking up 3rd party modules
        $modulesPaths = array(Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'modules', Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'modules_core');

        // Execute Autoloaders in each modules paths
        foreach ($modulesPaths as $modulesPath) {

            // Scan Modules
            $modules = scandir($modulesPath);
            foreach ($modules as $module) {
                if ($module == '.' || $module == '..')
                    continue;

                $moduleDir = $modulesPath . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR;

                if (is_dir($moduleDir) && is_file($moduleDir . 'autostart.php')) {

                    // Store Filename to Cache Content
                    $fileNames[] = $moduleDir . 'autostart.php';

                    // Execute Autoloader
                    require_once($moduleDir . 'autostart.php');
                }
            }
        }

        if ($cacheEnabled) {
            // Created a cache file which contains all autoloaders
            $content = "";
            foreach ($fileNames as $fileName) {
                $content .= file_get_contents($fileName);
            }
            file_put_contents($cacheFileName, $content);
        }
    }

    /**
     * Loads all enabled modules from the database. (Cached)
     */
    private function loadEnabledModules() {

        $cacheId = "enabledModules";
        $cacheValue = Yii::app()->cache->get($cacheId);

        if ($cacheValue === false || !is_array($cacheValue)) {

            $enabledModules = array();
            foreach (ModuleEnabled::model()->findAll() as $em) {
                $enabledModules[$em->module_id] = $em->module_id;
            }
            Yii::app()->cache->set($cacheId, $enabledModules, HSetting::Get('expireTime', 'cache'));
            $this->enabledModules = $enabledModules;
        } else {
            $this->enabledModules = $cacheValue;
        }
    }

    /**
     * Flushes Module Managers cache
     */
    public static function flushCache() {

        // Autoloader Cache File
        $cacheFileName = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . self::AUTOSTART_CACHE_FILE_NAME;
        if (file_exists($cacheFileName)) {
            unlink($cacheFileName);
        }

        $cacheId = "enabledModules";
        Yii::app()->cache->delete($cacheId);
    }

    /**
     * Registers a module
     * This is usally called in the autostart file of the module.
     *
     * @param Array $definition
     */
    public function register($definition) {
        $id = $definition['id'];

        if (!isset($definition['isSpaceModule']))
            $definition['isSpaceModule'] = false;

        if (!isset($definition['isCoreModule']))
            $definition['isCoreModule'] = false;

        if (!isset($definition['configRoute']))
            $definition['configRoute'] = '';

        if (!isset($definition['spaceConfigRoute']))
            $definition['spaceConfigRoute'] = '';


        $this->registeredModules[$id] = $definition;

        // Check if module is enabled
        if (Yii::app()->moduleManager->isEnabled($id)) {

            // Register Yii Module
            if (isset($definition['class'])) {
                Yii::app()->setModules(array(
                    $id => array(
                        'class' => $definition['class']
                    ),
                ));
            }

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
    }

    /**
     * Checks if a module is enabled or not.
     *
     * @param type $moduleId
     * @return boolean
     */
    public function isEnabled($moduleId) {

        $definition = $this->getDefinition($moduleId);

        if ($definition['isCoreModule'])
            return true;

        // Core installed yet?
        if (!Yii::app()->params['installed'])
            return false;

        if (in_array($moduleId, $this->enabledModules)) {
            return true;
        }

        #$moduleEnabled = ModuleEnabled::model()->findByPk($moduleId);
        #if ($moduleEnabled != null) {
        #    return true;
        #}

        return false;
    }

    /**
     * Returns an array with all registered modules
     * This contains all enabled & disabled modules.
     * Key is the moduleId and value is the module definition
     *
     * @return type
     */
    public function getRegisteredModules() {
        return $this->registeredModules;
    }

    /**
     * Returns an array with enabled modules
     * Key of the array is the module id and value is the module definition.
     *
     * @return array
     */
    public function getEnabledModules() {

        $enabledModules = array();

        foreach ($this->getRegisteredModules() as $moduleId => $definition) {
            if ($this->isEnabled($moduleId)) {
                $enabledModules[$moduleId] = $definition;
            }
        }

        return $enabledModules;
    }

    /**
     * Enables a module by given module id.
     *
     * @param String $id
     */
    public function enable($id) {

        $definition = $this->getDefinition($id);
        if ($definition != null) {

            // Core Modules doesn´t need to enabled
            if (!$definition['isCoreModule']) {

                $moduleEnabled = ModuleEnabled::model()->findByPk($id);
                if ($moduleEnabled == null) {

                    $moduleEnabled = new ModuleEnabled();
                    $moduleEnabled->module_id = $id;
                    $moduleEnabled->save();

                    // Auto Migrate (add module database changes)
                    Yii::import('application.commands.shell.ZMigrateCommand');
                    $migrate = ZMigrateCommand::AutoMigrate();

                    // Fire Event Disabled Event
                    if ($this->hasEventHandler('onEnable'))
                        $this->onEnable(new CEvent($this, $id));
                }
            }
        }

        ModuleManager::flushCache();
    }

    /**
     * Disables a active module by given module id
     *
     * @param String $id
     */
    public function disable($id) {

        $definition = $this->getDefinition($id);
        if ($definition != null) {

            // Core Modules couldn´t disabled
            if (!$definition['isCoreModule']) {

                if (isset($definition['userModules']) && is_array($definition['userModules'])) {
                    $modulesToDisable = array_keys($definition['userModules']);
                    foreach (User::model()->findAll() as $user) {
                        foreach ($modulesToDisable as $userModuleId) {
                            if ($user->isModuleEnabled($userModuleId))
                                $user->uninstallModule($userModuleId);
                        }
                    }
                }

                if (isset($definition['spaceModules']) && is_array($definition['spaceModules'])) {
                    $modulesToDisable = array_keys($definition['spaceModules']);
                    foreach (Space::model()->findAll() as $space) {
                        foreach ($modulesToDisable as $spaceModuleId) {
                            if ($space->isModuleEnabled($spaceModuleId))
                                $space->uninstallModule($spaceModuleId);
                        }
                    }
                }

                // Get Enabled Module Record
                $moduleEnabled = ModuleEnabled::model()->findByPk($id);
                if ($moduleEnabled != null)
                    $moduleEnabled->delete();

                // Fire Event Disabled Event
                if ($this->hasEventHandler('onDisable'))
                    $this->onDisable(new CEvent($this, $id));
            }
        }
        ModuleManager::flushCache();
    }

    /**
     * This event is raised after disabling a module
     *
     * @param CEvent $event the event parameter
     * @see disable
     */
    public function onDisable($event) {
        $this->raiseEvent('onDisable', $event);
    }

    /**
     * This event is raised after enabling a module
     *
     * @param CEvent $event the event parameter
     * @see enable
     */
    public function onEnable($event) {
        $this->raiseEvent('onEnable', $event);
    }

    /**
     * Returns the definition array of a registered module
     *
     * @param type $id
     * @return null
     */
    public function getDefinition($id) {
        if (isset($this->registeredModules[$id]))
            return $this->registeredModules[$id];

        return null;
    }

    /**
     * Registers a new Content Model
     *
     * @param String $className
     */
    public function registerContentModel($className) {
        $this->registeredContentModels[] = $className;
    }

    /**
     * Returns a list of all registered content models
     *
     * @return Array
     */
    public function getContentModels() {
        return $this->registeredContentModels;
    }

}

?>
