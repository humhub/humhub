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
 * SpaceModelModuleBehavior handles all space model relating moduling methods.
 * (Install, Uninstall modules)
 *
 * @since 0.6
 * @package humhub.modules_core.space.behaviors
 * @author luke
 */
class SpaceModelModulesBehavior extends CActiveRecordBehavior
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

        foreach (Yii::app()->moduleManager->getEnabledModules() as $moduleId => $module) {
            if ($module->isSpaceModule()) {
                $this->_availableModules[$module->getId()] = $module;
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
        $defaultStates = SpaceApplicationModule::getStates();
        $states = SpaceApplicationModule::getStates($this->getOwner()->id);

        // Get a list of all enabled module ids
        foreach (array_merge(array_keys($defaultStates), array_keys($states)) as $id) {

            // Ensure module Id is available
            if (!array_key_exists($id, $availableModules)) {
                continue;
            }

            if (isset($defaultStates[$id]) && $defaultStates[$id] == SpaceApplicationModule::STATE_FORCE_ENABLED) {
                // Forced enabled globally
                $this->_enabledModules[] = $id;
            } elseif (!isset($states[$id]) && isset($defaultStates[$id]) && $defaultStates[$id] == SpaceApplicationModule::STATE_ENABLED) {
                // No local state -> global default on
                $this->_enabledModules[] = $id;
            } elseif (isset($states[$id]) && $states[$id] == SpaceApplicationModule::STATE_ENABLED) {
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
            Yii::log("Space->enableModule(" . $moduleId . ") module is already enabled");
            return false;
        }

        // Add Binding
        $spaceModule = SpaceApplicationModule::model()->findByAttributes(array('space_id' => $this->getOwner()->id, 'module_id' => $moduleId));
        if ($spaceModule == null) {
            $spaceModule = new SpaceApplicationModule();
            $spaceModule->space_id = $this->getOwner()->id;
            $spaceModule->module_id = $moduleId;
        }        
        $spaceModule->state = SpaceApplicationModule::STATE_ENABLED;
        $spaceModule->save();

        $module = Yii::app()->moduleManager->getModule($moduleId);
        $module->enableSpaceModule($this->getOwner());

        return true;
    }

    public function canDisableModule($id)
    {
        $defaultStates = SpaceApplicationModule::getStates(0);
        if (isset($defaultStates[$id]) && $defaultStates[$id] == SpaceApplicationModule::STATE_FORCE_ENABLED) {
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
            Yii::log("Space->disableModule(" . $moduleId . ") module is not enabled");
            return false;
        }

        // New Way: Handle it directly in module class
        $module = Yii::app()->moduleManager->getModule($moduleId);
        $module->disableSpaceModule($this->getOwner());

        $spaceModule = SpaceApplicationModule::model()->findByAttributes(array('space_id' => $this->getOwner()->id, 'module_id' => $moduleId));
        if ($spaceModule == null) {
            $spaceModule = new SpaceApplicationModule();
            $spaceModule->space_id = $this->getOwner()->id;
            $spaceModule->module_id = $moduleId;
        }
        $spaceModule->state = SpaceApplicationModule::STATE_DISABLED;
        $spaceModule->save();

        return true;
    }

}
