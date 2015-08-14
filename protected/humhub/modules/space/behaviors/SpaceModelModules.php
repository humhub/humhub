<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\behaviors;

use Yii;
use yii\base\Behavior;
use humhub\modules\space\models\Space;
use humhub\modules\content\components\ContentContainerModule;
/**
 * SpaceModelModuleBehavior handles all space model relating moduling methods.
 * (Install, Uninstall modules)
 *
 * @since 0.6
 * @package humhub.modules_core.space.behaviors
 * @author luke
 */
class SpaceModelModules extends Behavior
{

    public $_enabledModules = null;
    public $_availableModules = null;

    /**
     * Collects a list of all modules which are available for this space
     *
     * @return array
     */
    public function getAvailableModules()
    {

        if ($this->_availableModules !== null) {
            return $this->_availableModules;
        }

        $this->_availableModules = array();

        foreach (Yii::$app->moduleManager->getModules() as $moduleId => $module) {
            if ($module instanceof ContentContainerModule && Yii::$app->hasModule($module->id) && $module->hasContentContainerType(Space::className())) {
                $this->_availableModules[$module->id] = $module;
            }
        }

        return $this->_availableModules;
    }

    /**
     * Returns an array of enabled space modules
     *
     * @return array
     */
    public function getEnabledModules()
    {

        if ($this->_enabledModules !== null) {
            return $this->_enabledModules;
        }

        $this->_enabledModules = array();

        $availableModules = $this->getAvailableModules();
        $defaultStates = \humhub\modules\space\models\Module::getStates();
        $states = \humhub\modules\space\models\Module::getStates($this->owner->id);

        // Get a list of all enabled module ids
        foreach (array_merge(array_keys($defaultStates), array_keys($states)) as $id) {

            // Ensure module Id is available
            if (!array_key_exists($id, $availableModules)) {
                continue;
            }

            if (isset($defaultStates[$id]) && $defaultStates[$id] == \humhub\modules\space\models\Module::STATE_FORCE_ENABLED) {
                // Forced enabled globally
                $this->_enabledModules[] = $id;
            } elseif (!isset($states[$id]) && isset($defaultStates[$id]) && $defaultStates[$id] == \humhub\modules\space\models\Module::STATE_ENABLED) {
                // No local state -> global default on
                $this->_enabledModules[] = $id;
            } elseif (isset($states[$id]) && $states[$id] == \humhub\modules\space\models\Module::STATE_ENABLED) {
                // Locally enabled
                $this->_enabledModules[] = $id;
            }
        }

        return $this->_enabledModules;
    }

    /**
     * Checks if given ModuleId is enabled
     *
     * @param type $moduleId
     */
    public function isModuleEnabled($moduleId)
    {
        return in_array($moduleId, $this->getEnabledModules());
    }

    /**
     * Enables a Module
     */
    public function enableModule($moduleId)
    {

        // Not enabled globally
        if (!array_key_exists($moduleId, $this->getAvailableModules())) {
            return false;
        }

        // Already enabled module
        if ($this->isModuleEnabled($moduleId)) {
            Yii::error("Space->enableModule(" . $moduleId . ") module is already enabled");
            return false;
        }

        // Add Binding
        $spaceModule = \humhub\modules\space\models\Module::findOne(['space_id' => $this->owner->id, 'module_id' => $moduleId]);
        if ($spaceModule == null) {
            $spaceModule = new \humhub\modules\space\models\Module();
            $spaceModule->space_id = $this->owner->id;
            $spaceModule->module_id = $moduleId;
        }
        $spaceModule->state = \humhub\modules\space\models\Module::STATE_ENABLED;
        $spaceModule->save();

        $module = Yii::$app->moduleManager->getModule($moduleId);
        $module->enableContentContainer($this->owner);

        return true;
    }

    public function canDisableModule($id)
    {
        $defaultStates = \humhub\modules\space\models\Module::getStates(0);
        if (isset($defaultStates[$id]) && $defaultStates[$id] == \humhub\modules\space\models\Module::STATE_FORCE_ENABLED) {
            return false;
        }

        return true;
    }

    /**
     * Uninstalls a Module
     */
    public function disableModule($moduleId)
    {

        // Not enabled globally
        if (!array_key_exists($moduleId, $this->getAvailableModules())) {
            return false;
        }

        // Already enabled module
        if (!$this->isModuleEnabled($moduleId)) {
            Yii::error("Space->disableModule(" . $moduleId . ") module is not enabled");
            return false;
        }

        // New Way: Handle it directly in module class
        $module = Yii::$app->moduleManager->getModule($moduleId);
        $module->disableContentContainer($this->owner);

        $spaceModule = \humhub\modules\space\models\Module::findOne(['space_id' => $this->owner->id, 'module_id' => $moduleId]);
        if ($spaceModule == null) {
            $spaceModule = new \humhub\modules\space\models\Module();
            $spaceModule->space_id = $this->owner->id;
            $spaceModule->module_id = $moduleId;
        }
        $spaceModule->state = \humhub\modules\space\models\Module::STATE_DISABLED;
        $spaceModule->save();

        return true;
    }

}
