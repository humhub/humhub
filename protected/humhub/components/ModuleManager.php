<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/** @noinspection UnknownInspectionInspection */

namespace humhub\components;

use ArrayAccess;
use humhub\components\bootstrap\ModuleAutoLoader;
use humhub\components\console\Application as ConsoleApplication;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\models\ModuleEnabled;
use humhub\modules\admin\events\ModulesEvent;
use humhub\modules\marketplace\Module as ModuleMarketplace;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\Event;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\ServerErrorHttpException;

/**
 * ModuleManager handles all installed modules.
 *
 * @author luke
 */
class ModuleManager extends Component
{
    /**
     * @event triggered before a module is enabled
     * @since 1.3
     */
    public const EVENT_BEFORE_MODULE_ENABLE = 'beforeModuleEnabled';

    /**
     * @event triggered after a module is enabled
     * @since 1.3
     */
    public const EVENT_AFTER_MODULE_ENABLE = 'afterModuleEnabled';

    /**
     * @event triggered before a module is disabled
     * @since 1.3
     */
    public const EVENT_BEFORE_MODULE_DISABLE = 'beforeModuleDisabled';

    /**
     * @event triggered after a module is disabled
     * @since 1.3
     */
    public const EVENT_AFTER_MODULE_DISABLE = 'afterModuleDisabled';

    /**
     * @event triggered after filter modules
     * @since 1.11
     */
    public const EVENT_AFTER_FILTER_MODULES = 'afterFilterModules';

    /**
     * Create a backup on module folder deletion
     *
     * @var bool
     */
    public bool $createBackup = true;

    /**
     * List of all modules
     * This also contains installed but not enabled modules.
     *
     * @param array $modules moduleId-class pairs
     */
    protected array $modules = [];

    /**
     * List of all enabled module ids
     *
     * @var array
     */
    protected array $enabledModules = [];

    /**
     * List of core module classes.
     *
     * @var array the core module class names
     */
    protected array $coreModules = [];

    /**
     * @var bool Prevent registration of several different modules with the same id.
     */
    public bool $preventDuplicatedModules = true;

    /**
     * List of module paths that should be overwritten
     * Key - module id, Value - absolute path to module folder
     *
     * @var array
     */
    public array $overwriteModuleBasePath = [];

    /**
     * Module Manager init
     *
     * Loads all enabled moduleId's from database
     */
    public function init()
    {
        parent::init();

        // Either database installed and not in installed state
        if (!Yii::$app->isInstalled() && !Yii::$app->isDatabaseInstalled()) {
            return;
        }

        if (!Yii::$app->isDatabaseInstalled()) {
            $this->enabledModules = [];
        } else {
            $this->enabledModules = ModuleEnabled::getEnabledIds();
        }
    }

    /**
     * Registers a module to the manager
     * This is usually done by config.php in modules root folder.
     *
     * @param array $configs
     *
     * @throws InvalidConfigException Module configuration does not have both an id and class attribute
     * @see ModuleAutoLoader::bootstrap
     *
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
     * @param string $basePath the module's base path
     * @param array|null $config the module configuration (config.php)
     *
     * @throws InvalidConfigException Module configuration does not have both an id and class attribute
     */
    public function register(string $basePath, ?array $config = null)
    {
        if ($config === null && is_file($filename = $basePath . '/config.php')) {
            $config = include $filename;
        }

        // Check mandatory config options
        if (!isset($config['class'], $config['id'])) {
            throw new InvalidConfigException('Module configuration requires an id and class attribute: ' . $basePath);
        }

        $isCoreModule = (isset($config['isCoreModule']) && $config['isCoreModule']);
        $isInstallerModule = (isset($config['isInstallerModule']) && $config['isInstallerModule']);

        if (
            !$isCoreModule
            && !$isInstallerModule
            && !empty(Yii::$app->loadedAppConfig['modules'][$config['id']])
            && !in_array($config['id'], $this->enabledModules, true)
        ) {
            $errorMessage = Yii::t('error', 'The module {moduleId} is present in the HumHub configuration file even though this module is disabled. Please remove it from the configuration.', ['moduleId' => '“' . $config['id'] . '”']);
            Yii::error($errorMessage);
            Yii::$app->view?->error($errorMessage);
            return null;
        }

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

        if (!Yii::$app->isInstalled() && $isInstallerModule) {
            $this->enabledModules[] = $config['id'];
        }

        // Not enabled and no core/installer module
        if (!$isCoreModule && !in_array($config['id'], $this->enabledModules)) {
            return $config['id'];
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
        $this->registerEventHandlers($basePath, $config);

        // Register Console ControllerMap
        if (Yii::$app instanceof ConsoleApplication && !(empty($config['consoleControllerMap']))) {
            Yii::$app->controllerMap = ArrayHelper::merge(Yii::$app->controllerMap, $config['consoleControllerMap']);
        }

        return $config['id'];
    }

    /**
     * @throws InvalidConfigException
     */
    protected function registerEventHandlers(string $basePath, array &$config): void
    {
        $events = $config['events'] ?? null;
        $strict = $config['strict'] ?? false;

        if (empty($events)) {
            return;
        }

        $error = static function (string $message, bool $throw = false) use (&$config, $basePath) {
            $message = sprintf("Configuration at %s has an invalid event configuration: %s", $basePath, $message);

            if ($throw) {
                throw new InvalidConfigException($message);
            }

            Yii::warning($message, $config['id']);
        };

        if (!ArrayHelper::isTraversable($events)) {
            $error('events must be traversable', $strict);
            return;
        }

        $getProperty = static function ($event, &$var, string $property, int $index, bool $throw = false) use ($error): bool {

            $var = $event[$property] ?? $event[$index] ?? null;

            if (empty($var)) {
                $error(sprintf("required property '%s' missing!", $property), $throw);
                return false;
            }

            return true;
        };

        foreach ($events as $event) {
            if (empty($event)) {
                continue;
            }

            if (!is_array($event) && !$event instanceof ArrayAccess) {
                $error('event configuration must be an array or implement \ArrayAccess', $strict);
                break;
            }

            if (!$getProperty($event, $eventClass, 'class', 0, $strict)) {
                continue;
            }

            if (!$getProperty($event, $eventName, 'event', 1, $strict)) {
                continue;
            }

            if (!$getProperty($event, $eventHandler, 'callback', 2, $strict)) {
                continue;
            }

            if (!is_array($eventHandler)) {
                $error("property 'callback' must be a callable defined in the array-notation denoting a method of a class", $strict);
                continue;
            }

            if (!is_object($eventHandler[0] ?? null) && !class_exists($eventHandler[0] ?? null)) {
                $error(sprintf("class '%s' does not exist.", $eventHandler[0] ?? ''), $strict);
                continue;
            }

            if (!method_exists($eventHandler[0], $eventHandler[1])) {
                $error(
                    sprintf(
                        "class '%s' does not have a method called '%s",
                        is_object($eventHandler[0]) ? get_class($eventHandler[0]) : $eventHandler[0],
                        $eventHandler[1],
                    ),
                    $strict,
                );
                continue;
            }

            $eventData = $event['data'] ?? $event[3] ?? null;
            $eventAppend = filter_var($event['append'] ?? $event[4] ?? true, FILTER_VALIDATE_BOOLEAN);

            Event::on($eventClass, $eventName, $eventHandler, $eventData, $eventAppend);
        }

        $events = null;
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
        $options = array_merge([
            'includeCoreModules' => false,
            'enabled' => false,
            'returnClass' => false,
        ], $options);

        $modules = [];
        foreach ($this->modules as $id => $class) {
            if (!$options['includeCoreModules'] && in_array($class, $this->coreModules, true)) {
                // Skip core modules
                continue;
            }

            if (
                $options['enabled']
                && !in_array($class, $this->coreModules, true)
                && !in_array($id, $this->enabledModules, true)
            ) {
                // Skip disabled modules
                continue;
            }

            if ($options['returnClass']) {
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
     * Filter modules by keyword and by additional filters from module event
     *
     * @param Module[]|null $modules
     * @param array|ArrayAccess $filters = ['keyword' => 'search term']
     *
     * @return Module[]
     */
    public function filterModules(?array $modules, $filters = []): array
    {
        if (!$filters instanceof ArrayAccess && !is_array($filters)) {
            throw new InvalidArgumentTypeException('$filters', ['array', ArrayAccess::class], $filters);
        }

        $modules = $this->filterModulesByKeyword($modules, $filters['keyword'] ?? null);

        $modulesEvent = new ModulesEvent(['modules' => $modules]);
        $this->trigger(static::EVENT_AFTER_FILTER_MODULES, $modulesEvent);

        return $modulesEvent->modules;
    }

    /**
     * Filter modules by keyword
     *
     * @param Module[]|null $modules list of modules, defaulting to installed non-core modules
     * @param null|string $keyword
     *
     * @return Module[]
     */
    public function filterModulesByKeyword(?array $modules, $keyword = null): array
    {
        $modules ??= $this->getModules();

        if ($keyword === null) {
            $keyword = Yii::$app->request->get('keyword', '');
        }

        if (!is_scalar($keyword) || $keyword === '') {
            return $modules;
        }

        foreach ($modules as $id => $module) {
            /* @var Module $module */
            $searchFields = [$id];
            if ($searchField = $module->getName()) {
                $searchFields[] = $searchField;
            }

            if ($searchField = $module->getDescription()) {
                $searchFields[] = $searchField;
            }

            if ($searchField = $module->getKeywords()) {
                array_push($searchFields, ...$searchField);
            }

            $keywordFound = false;
            foreach ($searchFields as $searchField) {
                if (stripos($searchField, $keyword) !== false) {
                    $keywordFound = true;
                    break;
                }
            }

            if (!$keywordFound) {
                unset($modules[$id]);
            }
        }

        return $modules;
    }

    /**
     * Returns all enabled modules and supportes further options as [[getModules()]].
     *
     * @param array $options
     *
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
     * Checks if a moduleId exists, regardless it's enabled or not
     *
     * @param string $id
     *
     * @return bool
     */
    public function hasModule($id)
    {
        return array_key_exists($id, $this->modules)
            // Fallback to old module ID
            || array_key_exists(str_replace('-', '_', $id), $this->modules);
    }

    /**
     * Returns weather or not the given module id belongs to a core module.
     *
     * @return bool
     * @throws Exception
     * @since 1.3.8
     */
    public function isCoreModule($id)
    {
        if (!$this->hasModule($id)) {
            return false;
        }

        return (in_array(get_class($this->getModule($id)), $this->coreModules));
    }

    /**
     * Returns a module instance by id
     *
     * @param string $id Module Id
     * @param bool $throwOnMissingModule true - to throw exception, false - to return null
     *
     * @return Module|object|null
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function getModule($id, $throwOnMissingModule = true)
    {
        if ($id instanceof Module) {
            return $id;
        }

        // Enabled Module
        if (Yii::$app->hasModule($id)) {
            return Yii::$app->getModule($id, true);
        }

        if (!isset($this->modules[$id])) {
            // Fallback to old module ID
            $oldId = str_replace('-', '_', $id);
            if (isset($this->modules[$oldId])) {
                $id = $oldId;
            }
        }

        // Disabled Module
        if (isset($this->modules[$id])) {
            $class = $this->modules[$id];
            return Yii::createObject($class, [$id, Yii::$app]);
        }

        if (is_dir($id) && is_file($id . '/config.php')) {
            return $this->getModule($this->register($id));
        }

        if ($throwOnMissingModule) {
            throw new Exception('Could not find/load requested module: ' . $id);
        }

        return null;
    }

    /**
     * Flushes module manager cache
     */
    public function flushCache()
    {
        Yii::$app->cache->delete(ModuleAutoLoader::CACHE_ID);
    }

    /**
     * Checks if the module can be removed
     *
     * @param string $moduleId
     *
     * @noinspection PhpDocMissingThrowsInspection
     * */
    public function canRemoveModule($moduleId): bool
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $module = $this->getModule($moduleId, false);

        if ($module === null) {
            return false;
        }

        // Check is in dynamic/marketplace module folder
        /** @var ModuleMarketplace $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        if ($marketplaceModule !== null) {
            // Normalize paths before comparing in order to fix issues like Windows path separators `\`
            // Realpath is required when the modules path is a symlinked
            $modulePath = FileHelper::normalizePath($module->getBasePath());
            $aliasPath = FileHelper::normalizePath(realpath(Yii::getAlias($marketplaceModule->modulesPath)));
            if (strpos($modulePath, $aliasPath) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Removes a module
     *
     * @param string $moduleId
     * @param bool $disableBeforeRemove
     *
     * @throws Exception
     * @throws ErrorException
     */
    public function removeModule($moduleId, $disableBeforeRemove = true): ?string
    {
        $module = $this->getModule($moduleId);

        if ($module === null) {
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
            $backupFolderName = null;
            //TODO: Delete directory
        }

        $this->flushCache();

        return $backupFolderName;
    }

    /**
     * Enables a module
     *
     * @param Module $module
     *
     * @throws InvalidConfigException
     * @since 1.1
     */
    public function enable(Module $module)
    {
        $this->checkRequirements($module);

        $this->trigger(static::EVENT_BEFORE_MODULE_ENABLE, new ModuleEvent(['module' => $module]));

        if (!ModuleEnabled::findOne(['module_id' => $module->id])) {
            (new ModuleEnabled(['module_id' => $module->id]))->save();
        }

        $this->enabledModules[] = $module->id;
        $this->register($module->getBasePath());
        $this->flushCache();

        $this->trigger(static::EVENT_AFTER_MODULE_ENABLE, new ModuleEvent(['module' => $module]));
    }

    public function enableModules($modules = [])
    {
        foreach ($modules as $module) {
            $module = $this->getModule($module);
            if ($module != null) {
                $module->enable();
            }
        }
    }

    /**
     * Disables a module
     *
     * @param Module $module
     *
     * @throws Throwable
     * @throws StaleObjectException
     * @since 1.1
     */
    public function disable(Module $module)
    {
        $this->trigger(static::EVENT_BEFORE_MODULE_DISABLE, new ModuleEvent(['module' => $module]));

        $moduleEnabled = ModuleEnabled::findOne(['module_id' => $module->id]);
        if ($moduleEnabled !== null) {
            $moduleEnabled->delete();
        }

        if (($key = array_search($module->id, $this->enabledModules, true)) !== false) {
            unset($this->enabledModules[$key]);
        }

        Yii::$app->setModule($module->id, null);

        $this->flushCache();

        $this->trigger(static::EVENT_AFTER_MODULE_DISABLE, new ModuleEvent(['module' => $module]));
    }

    /**
     * @param array $modules
     *
     * @throws Exception
     */
    public function disableModules($modules = [])
    {
        foreach ($modules as $module) {
            $module = $this->getModule($module);
            if ($module !== null) {
                $module->disable();
            }
        }
    }

    /**
     * Check module requirements
     *
     * @param Module $module
     * @throws ServerErrorHttpException
     * @since 1.16
     */
    private function checkRequirements(Module $module)
    {
        $requirementsPath = $module->getBasePath() . DIRECTORY_SEPARATOR . 'requirements.php';
        if (!file_exists($requirementsPath)) {
            return;
        }

        $requirementCheckResult = include($requirementsPath);

        if (is_string($requirementCheckResult)) {
            Yii::error('Error enabling the "' . $module->id . '" module: ' . $requirementCheckResult, 'module');
            throw new ServerErrorHttpException($requirementCheckResult);
        }
    }
}
