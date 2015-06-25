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
 * Description of ModuleManager
 *
 * @author luke
 */
class ModuleManager extends \yii\base\Component
{

    private $installedModules;

    public function getEnabledModules()
    {

        return array();

        $modules = array();
        foreach ($this->enabledModules as $moduleId) {
            $module = $this->getModule($moduleId);
            if ($module != null) {
                $modules[] = $module;
            }
        }

        return $modules;
    }

    public function getInstalledModules()
    {
        return $modules;
    }

    public function disableModule($moduleId)
    {
        
    }

    public function enableModule($moduleId)
    {
        
    }

    public function register(Array $definition)
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
     * Returns the module path on given module class name or config
     */
    public function getModuleBasePath($moduleId)
    {
        $module = Yii::$app->getModule($moduleId, false);
        $className = '';

        
        if (is_array($module) && isset($module['class'])) {
            $className = $module['class'];
        } elseif ($module instanceof \yii\base\Module) {
            $className = $module->className();
        }

        if ($className === '') {
            print_r($module);
            print_r(array_keys(Yii::$app->getModules()));
            print "NOT FOUND".$moduleId."\n";
            
            print_r(Yii::$app->getModule('space', false));
            die();
            throw new Exception('Could not determine module class name!'.$moduleId);
        } else {
            print "FOUND FOR: ".$moduleId;
        }

        $reflector = new \ReflectionClass($className);
        return dirname($reflector->getFileName());
    }

}
