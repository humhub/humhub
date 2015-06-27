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

namespace humhub\core\user\behaviors;

use Yii;
use yii\base\Behavior;

/**
 * Extends User model with moduling functionalities.
 * Each user can enable, disable, install modules for his record/account.
 *
 * @since 0.6
 * @package humhub.modules_core.user.behaviors
 * @author luke
 */
class UserModelModules extends Behavior
{

    public $_enabledModules = null;
    public $_availableModules = null;

    /**
     * Returns a list of all available user modules
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
            if ($module->isUserModule()) {
                $this->_availableModules[$module->id] = $module;
            }
        }

        return $this->_availableModules;
    }

    /**
     * Returns an array of enabled user modules
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
        $defaultStates = \humhub\core\user\models\Module::getStates();
        $states = \humhub\core\user\models\Module::getStates($this->owner->id);

        // Get a list of all enabled module ids
        foreach (array_merge(array_keys($defaultStates), array_keys($states)) as $id) {

            // Ensure module Id is available
            if (!array_key_exists($id, $availableModules)) {
                continue;
            }

            if (isset($defaultStates[$id]) && $defaultStates[$id] == \humhub\core\user\models\Module::STATE_FORCE_ENABLED) {
                // Forced enabled globally
                $this->_enabledModules[] = $id;
            } elseif (!isset($states[$id]) && isset($defaultStates[$id]) && $defaultStates[$id] == \humhub\core\user\models\Module::STATE_ENABLED) {
                // No local state -> global default on
                $this->_enabledModules[] = $id;
            } elseif (isset($states[$id]) && $states[$id] == \humhub\core\user\models\Module::STATE_ENABLED) {
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
            Yii::log("User->enableModule(" . $moduleId . ") module is already enabled");
            return false;
        }

        // Add Binding
        $userModule = \humhub\core\user\models\Module::findOne(['user_id' => $this->owner->id, 'module_id' => $moduleId]);
        if ($userModule == null) {
            $userModule = new \humhub\core\user\models\Module();
            $userModule->user_id = $this->owner->id;
            $userModule->module_id = $moduleId;
        }
        $userModule->state = \humhub\core\user\models\Module::STATE_ENABLED;
        $userModule->save();

        $module = Yii::$app->moduleManager->getModule($moduleId);
        $module->enableUserModule($this->owner);

        return true;
    }

    public function canDisableModule($id)
    {
        $defaultStates = \humhub\core\user\models\Module::getStates();
        if (isset($defaultStates[$id]) && $defaultStates[$id] == \humhub\core\user\models\Module::STATE_FORCE_ENABLED) {
            return false;
        }

        return true;
    }

    /**
     * Disables a Module
     */
    public function disableModule($moduleId)
    {

        // Not enabled globally
        if (!array_key_exists($moduleId, $this->getAvailableModules())) {
            return false;
        }

        // Already enabled module
        if (!$this->isModuleEnabled($moduleId)) {
            Yii::log("User->disableModule(" . $moduleId . ") module is not enabled");
            return false;
        }

        // New Way: Handle it directly in module class
        $module = Yii::$app->moduleManager->getModule($moduleId);
        $module->disableUserModule($this->owner);

        $userModule = \humhub\core\user\models\Module::findOne(['user_id' => $this->owner->id, 'module_id' => $moduleId]);
        if ($userModule == null) {
            $userModule = new \humhub\core\user\models\Module;
            $userModule->user_id = $this->owner->id;
            $userModule->module_id = $moduleId;
        }
        $userModule->state = \humhub\core\user\models\Module::STATE_DISABLED;
        $userModule->save();

        return true;
    }

}
