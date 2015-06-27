<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;
use yii\base\Exception;
use yii\base\Event;

/**
 * ModuleManager handles all installed modules.
 *
 * @author luke
 */
class ModuleManager extends \yii\base\Component
{

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
     * @var Array
     */
    private $enabledModules = [];

    /**
     * Module Manager init
     * 
     * Loads all enabled moduleId's from database
     */
    public function init()
    {
        parent::init();

        if (!Yii::$app->params['installed'])
            return;

        foreach (\humhub\models\ModuleEnabled::find()->all() as $em) {
            $this->enabledModules[] = $em->module_id;
        }
    }

    /**
     * Registers a module to the manager
     * This is usally done by autostart.php in modules root folder.
     * 
     * @param array $definition
     * @throws Exception
     */
    public function register(Array $definition)
    {
        if (!isset($definition['class']) || !isset($definition['id'])) {
            throw new Exception("Register Module needs module Id and Class!");
        }

        $isCoreModule = (isset($definition['isCoreModule']) && $definition['isCoreModule']);

        $this->modules[$definition['id']] = $definition['class'];

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
            Yii::$app->urlManager->addRules($definition['urlManagerRules'], false);
        }

        // Register Yii Module
        Yii::$app->setModules(array(
            $definition['id'] => array(
                'class' => $definition['class'],
                'modules' => $definition['modules']
            ),
        ));

        // Register Event Handlers
        if (isset($definition['events'])) {
            foreach ($definition['events'] as $event) {
                Event::on($event['class'], $event['event'], $event['callback']);
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
     * 
     * @return array
     */
    public function getModules($options = [])
    {
        $modules = [];
        foreach ($this->modules as $id => $class) {

            // Skip core modules
            if (!isset($options['includeCoreModules']) || $options['includeCoreModules'] === false) {
                if (strpos($class, '\core\\') !== false) {
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
     * Checks if a moduleId exists
     *
     * @param string $id
     * @return boolean
     */
    public function hasModule($id)
    {
        return (array_key_exists($id, $this->modules));
    }

    /**
     * Returns a module instance by id
     *
     * @param string $id Module Id
     * @return \yii\base\Module
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

        throw new Exception("Could not find requested module: " . $id);
    }

}
