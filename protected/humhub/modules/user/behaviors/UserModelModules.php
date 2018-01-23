<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\behaviors;

use humhub\modules\user\models\User;
use humhub\modules\user\models\Module
use humhub\modules\content\components\ContentContainerModule;
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

    public $enabledModules = null;
    public $availableModules = null;

    /**
     * Returns a list of all available user modules
     *
     * @return array
     */
    public function getAvailableModules()
    {

        if ($this->availableModules !== null) {
            return $this->availableModules;
        }

        $this->availableModules = [];

        foreach (Yii::$app->moduleManager->getModules() as $moduleId => $module) {
            if ($module instanceof ContentContainerModule &&
                Yii::$app->hasModule($module->id) &&
                $module->hasContentContainerType(User::className())) {
                    $this->availableModules[$module->id] = $module;
            }
        }

        return $this->availableModules;
    }

    /**
     * Returns an array of enabled user modules
     *
     * @return array
     */
    public function getEnabledModules()
    {

        if ($this->enabledModules !== null) {
            return $this->enabledModules;
        }

        $this->enabledModules = [];

        $availableModules = $this->getAvailableModules();
        $defaultStates = Module::getStates();
        $states = Module::getStates($this->owner->id);

        // Get a list of all enabled module ids
        foreach (array_merge(array_keys($defaultStates), array_keys($states)) as $id) {

            // Ensure module Id is available
            if (!array_key_exists($id, $availableModules)) {
                continue;
            }

            if (isset($defaultStates[$id]) && $defaultStates[$id] == Module::STATE_FORCE_ENABLED) {
                // Forced enabled globally
                $this->enabledModules[] = $id;
            } elseif (!isset($states[$id]) && isset($defaultStates[$id]) &&
                      $defaultStates[$id] == Module::STATE_ENABLED) {
                          // No local state -> global default on
                          $this->enabledModules[] = $id;
            } elseif (isset($states[$id]) && $states[$id] == Module::STATE_ENABLED) {
                // Locally enabled
                $this->enabledModules[] = $id;
            }
        }

        return $this->enabledModules;
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
            Yii::error('User->enableModule(' . $moduleId . ') module is already enabled');
            return false;
        }

        // Add Binding
        $userModule = Module::findOne(['user_id' => $this->owner->id, 'module_id' => $moduleId]);
        if ($userModule == null) {
            $userModule = new Module();
            $userModule->user_id = $this->owner->id;
            $userModule->module_id = $moduleId;
        }
        $userModule->state = Module::STATE_ENABLED;
        $userModule->save();

        $module = Yii::$app->moduleManager->getModule($moduleId);
        $module->enableContentContainer($this->owner);

        return true;
    }

    public function canDisableModule($id)
    {
        $defaultStates = Module::getStates();
        if (isset($defaultStates[$id]) && $defaultStates[$id] == Module::STATE_FORCE_ENABLED) {
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
            Yii::error('User->disableModule(' . $moduleId . ') module is not enabled');
            return false;
        }

        // New Way: Handle it directly in module class
        $module = Yii::$app->moduleManager->getModule($moduleId);
        $module->disableContentContainer($this->owner);

        $userModule = Module::findOne(['user_id' => $this->owner->id, 'module_id' => $moduleId]);
        if ($userModule == null) {
            $userModule = new Module;
            $userModule->user_id = $this->owner->id;
            $userModule->module_id = $moduleId;
        }
        $userModule->state = Module::STATE_DISABLED;
        $userModule->save();

        return true;
    }

}
