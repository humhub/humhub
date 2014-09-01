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

    /**
     * Collects a list of all modules which are available for this space
     *
     * @return array
     */
    public function getAvailableModules()
    {
        $modules = array();

        foreach (Yii::app()->moduleManager->getEnabledModules() as $moduleId => $module) {
            if (array_key_exists('SpaceModuleBehavior', $module->behaviors())) {
                $modules[$module->getId()] = $module;
            }
        }

        return $modules;
    }

    /**
     * Returns an array of enabled workspace modules
     *
     * @return array
     */
    public function getEnabledModules()
    {

        $modules = array();
        foreach (SpaceApplicationModule::model()->findAllByAttributes(array('space_id' => $this->getOwner()->id)) as $SpaceModule) {
            $moduleId = $SpaceModule->module_id;

            if (Yii::app()->moduleManager->isEnabled($moduleId)) {
                $modules[] = $moduleId;
            }
        }

        return $modules;
    }

    /**
     * Checks if given ModuleId is enabled
     *
     * @param type $moduleId
     */
    public function isModuleEnabled($moduleId)
    {

        // Not enabled globally
        if (!array_key_exists($moduleId, $this->getAvailableModules())) {
            return false;
        }

        // Not enabled at space
        $module = SpaceApplicationModule::model()->findByAttributes(array('module_id' => $moduleId, 'space_id' => $this->getOwner()->id));
        if ($module == null) {
            return false;
        }

        return true;
    }

    /**
     * Installs a Module
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
        $SpaceModule = new SpaceApplicationModule();
        $SpaceModule->module_id = $moduleId;
        $SpaceModule->space_id = $this->getOwner()->id;
        $SpaceModule->save();

        $module = Yii::app()->moduleManager->getModule($moduleId);
        $module->enableSpaceModule($this->getOwner());

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

        SpaceApplicationModule::model()->deleteAllByAttributes(array('space_id' => $this->getOwner()->id, 'module_id' => $moduleId));

        return true;
    }

}
