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

    /**
     * List of all enabled module ids
     *
     * @var Array
     */
    private $enabledModules = [];

    /**
     * Array of installed modules populated on autostart.php register
     *
     * @var Array moduleId => moduleClass
     */
    private $installedModules;

    public function init()
    {
        parent::init();

        if (!Yii::$app->params['installed'])
            return;

        foreach (\humhub\models\ModuleEnabled::find()->all() as $em) {
            $this->enabledModules[] = $em->module_id;
        }
    }

    public function getEnabledModules()
    {
        return array();

        $modules = array();
        foreach ($this->enabledModules as $moduleId) {
            $module = Yii::$app->getModule($moduleId, false);
            if ($module != null) {
                $modules[] = $module;
            }
        }

        return $modules;
    }

    public function getInstalledModules($includeCoreModules = false, $returnClassName = false)
    {
        $installed = array();
        foreach ($this->installedModules as $moduleId => $className) {

            if (!$includeCoreModules && strpos($className, '\core\\') !== false) {
                continue;
            }

            if ($returnClassName) {
                $installed[$moduleId] = $className;
            } else {
                try {
                    $module = Yii::$app->getModule($moduleId, false);
                    if ($module !== null) {
                        $installed[$moduleId] = $module;
                    }
                } catch (Exception $ex) {
                    Yii::error('Could not instanciate module: ' . $moduleId . "." . $ex->getMessage(), 'error');
                }
            }
        }

        return $installed;
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

}
