<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\components\bootstrap\ModuleAutoLoader;
use humhub\libs\BaseSettingsManager;
use humhub\models\ModuleEnabled;
use Yii;
use yii\base\Component;
use yii\base\Event;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * ModuleManager handles all installed modules.
 *
 * @author luke
 */
class ModuleManager extends Component
{
    /**
     * @event triggered before a module is enabled
     */
    const EVENT_BEFORE_MODULE_ENABLE = 'beforeModuleEnabled';

    /**
     * @event triggered after a module is enabled
     */
    const EVENT_AFTER_MODULE_ENABLE = 'afterModuleEnabled';

    /**
     * @event triggered before a module is disabled
     */
    const EVENT_BEFORE_MODULE_DISABLE = 'beforeModuleDisabled';

    /**
     * @event triggered after a module is disabled
     */
    const EVENT_AFTER_MODULE_DISABLE = 'afterModuleDisabled';

    /**
     * Create a backup on module folder deletion
     *
     * @var boolean
     */
    public $createBackup = true;

    /**
     * List of all modules
     * This also contains installed but not enabled modules.
     *
     * @param array $config moduleId-class pairs
     */
    protected $modules;

    /**
     * List of all enabled module ids
     *
     * @var array
     */
    protected $enabledModules = [];

    /**
     * List of core module classes.
     *
     * @var array the core module class names
     */
    protected $coreModules = [];

    /**
     * Module Manager init
     *
     * Loads all enabled moduleId's from database
     */
    public function init()
    {
        parent::init();

        // Either database installed and not in installed state
        if (!Yii::$app->params['databaseInstalled'] && !Yii::$app->params['installed']) {
            return;
        }

        if (!BaseSettingsManager::isDatabaseInstalled()) {
            $this->enabledModules = [];
        } else {
            $this->enabledModules = ModuleEnabled::getEnabledIds();
        }
    }

    /**
     * Registers a module to the manager
     * This is usually done by config.php in modules root folder.
     * @see \humhub\components\bootstrap\ModuleAutoLoader::bootstrap
     *
     * @param array $configs
     * @throws InvalidConfigException
     */
    public function registerBulk(array $configs)
    {
        foreach ($configs as $basePath => $config) {
            $this->register($basePath, $config);
        }
    }

    /**
     * Registers a module
     *
     * @param string $basePath the modules base path
     * @param array $config the module configuration (config.php)
     * @throws InvalidConfigException
     */
    public function register($basePath, $config = null)
    {
        $filename = $basePath . '/config.php';
        if ($config === null && is_file($filename)) {
            $config = require $filename;
        }

        // Check mandatory config options
        if (!isset($config['class']) || !isset($config['id'])) {
            throw new InvalidConfigException('Module configuration requires an id and class attribute: '.$basePath);
        }

        $isCoreModule = (isset($config['isCoreModule']) && $config['isCoreModule']);
        $isInstallerModule = (isset($config['isInstallerModule']) && $config['isInstallerModule']);

        $this->modules[$config['id']] = $config['class'];

        if (isset($config['namespace'])) {
            Yii::setAlias('@' . str_replace('\\', '/', $config['namespace']), $basePath);
        }

        // Check if alias is not in use yet (e.g. don't register "web" module alias)
        if (Yii::getAlias('@' . $config['id'], false) === false) {
            Yii::setAlias('@' . $config['id'], $basePath);
        }

        if (isset($config['aliases']) && is_array($config['aliases'])) {
            foreach ($config['aliases'] as $name => $value) {
                Yii::setAlias($name, $value);
            }
        }

        if (!Yii::$app->params['installed'] && $isInstallerModule) {
            $this->enabledModules[] = $config['id'];
        }

        // Not enabled and no core/installer module
        if (!$isCoreModule && !in_array($config['id'], $this->enabledModules)) {
            return;
        }

        // Handle Submodules
        if (!isset($config['modules'])) {
            $config['modules'] = [];
        }

        if ($isCoreModule) {
            $this->coreModules[] = $config['class'];
        }

        // Append URL Rules
        if (isset($config['urlManagerRules'])) {
            Yii::$app->urlManager->addRules($config['urlManagerRules'], false);
        }

        $moduleConfig = [
            'class' => $config['class'],
            'modules' => $config['modules'],
        ];

        // Add config file values to module
        if (isset(Yii::$app->modules[$config['id']]) && is_array(Yii::$app->modules[$config['id']])) {
            $moduleConfig = ArrayHelper::merge($moduleConfig, Yii::$app->modules[$config['id']]);
        }

        // Register Yii Module
        Yii::$app->setModule($config['id'], $moduleConfig);

        // Register Event Handlers
        if (isset($config['events'])) {
            foreach ($config['events'] as $event) {
                if (isset($event['class'])) {
                    Event::on($event['class'], $event['event'], $event['callback']);
                } else {
                    Event::on($event[0], $event[1], $event[2]);
                }
            }
        }
    }

    /**
     * Returns all modules (also disabled modules).
     *
     * Note: Only modules which extends \humhub\components\Module will be returned.
     *
     * @param array $options options (name => config)
     * The following options are available:
     *
     * - includeCoreModules: boolean, return also core modules (default: false)
     * - returnClass: boolean, return classname instead of module object (default: false)
     * - enabled: boolean, returns only enabled modules (core modules only when combined with `includeCoreModules`)
     *
     * @return array
     * @throws Exception
     */
    public function getModules($options = [])
    {
        $modules = [];

        foreach ($this->modules as $id => $class) {

            // Skip core modules
            if (!isset($options['includeCoreModules']) || $options['includeCoreModules'] === false) {
                if (in_array($class, $this->coreModules)) {
                    continue;
                }
            }


            if (isset($options['enabled']) && $options['enabled'] === true) {
                if(!in_array($class, $this->coreModules) && !in_array($id, $this->enabledModules)) {
                    continue;
                }
            }

            if (isset($options['returnClass']) && $options['returnClass']) {
                $modules[$id] = $class;
            } else {
                $module = $this->getModule($id);
                if ($module instanceof Module) {
                    $modules[$id] = $module;
                }
            }
        }

        return $modules;
    }

    /**
     * Returns all enabled modules and supportes further options as [[getModules()]].
     *
     * @param array $options
     * @return array
     * @throws Exception
     * @since 1.3.10
     */
    public function getEnabledModules($options = [])
    {
        $options['enabled'] = true;
        return $this->getModules($options);
    }

    /**
     * Checks if a moduleId exists, regardless it's activated or not
     *
     * @param string $id
     * @return boolean
     */
    public function hasModule($id)
    {
        return (array_key_exists($id, $this->modules));
    }

    /**
     * Returns weather or not the given module id belongs to an core module.
     *
     * @return bool
     * @since 1.3.8
     * @throws Exception
     */
    public function isCoreModule($id)
    {
        if(!$this->hasModule($id)) {
            return false;
        }

        return (in_array(get_class($this->getModule($id)), $this->coreModules));
    }

    /**
     * Returns a module instance by id
     *
     * @param string $id Module Id
     * @return Module|object
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function getModule($id)
    {
        // Enabled Module
        if (Yii::$app->hasModule($id)) {
            return Yii::$app->getModule($id, true);
        }

        // Disabled Module
        if (isset($this->modules[$id])) {
            $class = $this->modules[$id];
            return Yii::createObject($class, [$id, Yii::$app]);
        }

        throw new Exception('Could not find/load requested module: ' . $id);
    }

    /**
     * Flushes module manager cache
     */
    public function flushCache()
    {
        Yii::$app->cache->delete(ModuleAutoLoader::CACHE_ID);
    }

    /**
     * Checks the module can removed
     *
     * @param string $moduleId
     * @return bool
     * @throws Exception
     */
    public function canRemoveModule($moduleId)
    {
        $module = $this->getModule($moduleId);

        if ($module === null) {
            return false;
        }

        // Check is in dynamic/marketplace module folder
        if (strpos($module->getBasePath(), Yii::getAlias(Yii::$app->getModule('marketplace')->modulesPath)) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Removes a module
     *
     * @param string $moduleId
     * @param bool $disableBeforeRemove
     * @throws Exception
     * @throws \yii\base\ErrorException
     */
    public function removeModule($moduleId, $disableBeforeRemove = true)
    {
        $module = $this->getModule($moduleId);

        if ($module == null) {
            throw new Exception('Could not load module to remove!');
        }

        /**
         * Disable Module
         */
        if ($disableBeforeRemove && Yii::$app->hasModule($moduleId)) {
            $module->disable();
        }

        /**
         * Remove Folder
         */
        if ($this->createBackup) {
            $moduleBackupFolder = Yii::getAlias('@runtime/module_backups');
            FileHelper::createDirectory($moduleBackupFolder);

            $backupFolderName = $moduleBackupFolder . DIRECTORY_SEPARATOR . $moduleId . '_' . time();
            $moduleBasePath = $module->getBasePath();
            FileHelper::copyDirectory($moduleBasePath, $backupFolderName);
            FileHelper::removeDirectory($moduleBasePath);
        } else {
            //TODO: Delete directory
        }

        $this->flushCache();
    }

    /**
     * Enables a module
     *
     * @param Module $module
     * @throws InvalidConfigException
     * @since 1.1
     */
    public function enable(Module $module)
    {
        $this->trigger(static::EVENT_BEFORE_MODULE_ENABLE, new ModuleEvent(['module' => $module]));

        if (!ModuleEnabled::findOne(['module_id' => $module->id])) {
            (new ModuleEnabled(['module_id' => $module->id]))->save();
        }

        $this->enabledModules[] = $module->id;
        $this->register($module->getBasePath());

        $this->trigger(static::EVENT_AFTER_MODULE_ENABLE, new ModuleEvent(['module' => $module]));
    }

    public function enableModules($modules = [])
    {
        foreach ($modules as $module) {
            $module = ($module instanceof Module) ? $module : $this->getModule($module);
            if ($module != null) {
                $module->enable();
            }
        }
    }

    /**
     * Disables a module
     *
     * @param Module $module
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @since 1.1
     */
    public function disable(Module $module)
    {
        $this->trigger(static::EVENT_BEFORE_MODULE_DISABLE, new ModuleEvent(['module' => $module]));

        $moduleEnabled = ModuleEnabled::findOne(['module_id' => $module->id]);
        if ($moduleEnabled != null) {
            $moduleEnabled->delete();
        }

        if (($key = array_search($module->id, $this->enabledModules)) !== false) {
            unset($this->enabledModules[$key]);
        }

        Yii::$app->setModule($module->id, null);

        $this->trigger(static::EVENT_AFTER_MODULE_DISABLE, new ModuleEvent(['module' => $module]));
    }

    /**
     * @param array $modules
     * @throws Exception
     */
    public function disableModules($modules = [])
    {
        foreach ($modules as $module) {
            $module = ($module instanceof Module) ? $module : $this->getModule($module);
            if ($module != null) {
                $module->disable();
            }
        }
    }
}
