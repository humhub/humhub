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
 * Extends User model with moduling functionalities.
 * Each user can enable, disable, install modules for his record/account.
 *
 * @since 0.6
 * @package humhub.modules_core.user.behaviors
 * @author luke
 */
class UserModelModulesBehavior extends CActiveRecordBehavior
{

    /**
     * Returns a list of available workspace modules
     *
     * @return array
     */
    public function getAvailableModules()
    {

        $modules = array();

        foreach (Yii::app()->moduleManager->getEnabledModules() as $moduleId => $module) {
            if (array_key_exists('UserModuleBehavior', $module->behaviors())) {
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
        foreach (UserApplicationModule::model()->findAllByAttributes(array('user_id' => $this->getOwner()->id)) as $userModule) {
            $moduleId = $userModule->module_id;

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
        $module = UserApplicationModule::model()->findByAttributes(array('module_id' => $moduleId, 'user_id' => $this->getOwner()->id));
        if ($module == null) {
            return false;
        }

        return true;
    }

    /**
     * Installs a Module
     */
    public function installModule($moduleId)
    {

        // Not enabled globally
        if (!array_key_exists($moduleId, $this->getAvailableModules())) {
            return false;
        }

        // Already enabled module
        if ($this->isModuleEnabled($moduleId)) {
            Yii::log("User->installModule(" . $moduleId . ") module is already enabled");
            return false;
        }

        // Add Binding
        $userModule = new UserApplicationModule();
        $userModule->module_id = $moduleId;
        $userModule->user_id = $this->getOwner()->id;
        $userModule->save();

        $module = Yii::app()->moduleManager->getModule($moduleId);
        $module->enableUserModule($this->getOwner());

        return true;
    }

    /**
     * Uninstalls a Module
     */
    public function uninstallModule($moduleId)
    {

        // Not enabled globally
        if (!array_key_exists($moduleId, $this->getAvailableModules())) {
            return false;
        }

        // Already enabled module
        if (!$this->isModuleEnabled($moduleId)) {
            Yii::log("User->uninstallModule(" . $moduleId . ") module is not enabled");
            return false;
        }

        UserApplicationModule::model()->deleteAllByAttributes(array('user_id' => $this->getOwner()->id, 'module_id' => $moduleId));

        $module = Yii::app()->moduleManager->getModule($moduleId);
        $module->disableUserModule($this->getOwner());

        return true;
    }

}
