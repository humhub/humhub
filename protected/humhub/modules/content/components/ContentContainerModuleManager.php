<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use ReflectionClass;
use Yii;
use yii\db\ActiveQuery;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\models\ContentContainerModuleState;
use humhub\modules\content\models\ContentContainer;

/**
 * ModuleManager handles modules of a content container.
 * 
 * @since 1.3
 * @author Luke
 */
class ContentContainerModuleManager extends \yii\base\Component
{

    /**
     * @var \humhub\modules\content\components\ContentContainerActiveRecord
     */
    public $contentContainer;

    /**
     * @var array the available module ids
     */
    private $_available;

    /**
     * Disables a module for the content container
     * 
     * @param string $id the module id
     * @return boolean
     */
    public function disable($id)
    {
        if ($this->canDisable($id)) {
            Yii::$app->moduleManager->getModule($id)->disableContentContainer($this->contentContainer);

            $moduleState = $this->getModuleStateRecord($id);
            $moduleState->module_state = ContentContainerModuleState::STATE_DISABLED;
            $moduleState->save();

            return true;
        }

        return false;
    }

    /**
     * Enables a module for this content container 
     * 
     * @param string $id the module id
     * @return boolean
     */
    public function enable($id)
    {
        if ($this->canEnable($id)) {
            Yii::$app->moduleManager->getModule($id)->enableContentContainer($this->contentContainer);

            $moduleState = $this->getModuleStateRecord($id);
            $moduleState->module_state = ContentContainerModuleState::STATE_ENABLED;
            $moduleState->save();

            return true;
        }

        return false;
    }

    /**
     * Checks whether the module is activated or not
     * 
     * @param string $id the module id
     * @return boolean 
     */
    public function isEnabled($id)
    {
        return in_array($id, $this->getEnabled());
    }

    /**
     * Checks whether the module can be enabled or not
     * 
     * @param string $id the module id
     * @return boolean
     */
    public function canEnable($id)
    {
        $available = $this->getAvailable();
        if (!$this->isEnabled($id) && array_key_exists($id, $available)) {
            return true;
        }

        return false;
    }

    /**
     * Checks whether the module can be disabled or not
     * 
     * @param string $id the module id
     * @return boolean
     */
    public function canDisable($id)
    {
        if (!$this->isEnabled($id) || self::getDefaultState($this->contentContainer->className(), $id) === ContentContainerModuleState::STATE_FORCE_ENABLED) {
            return false;
        }

        return true;
    }

    /**
     * Returns an array of all enabled module ids
     * 
     * @return array a list of enabled module ids
     */
    public function getEnabled()
    {
        $enabled = [];
        $available = $this->getAvailable();
        foreach ($this->getStates() as $id => $state) {
            if (array_key_exists($id, $available) && ($state === ContentContainerModuleState::STATE_ENABLED || $state === ContentContainerModuleState::STATE_FORCE_ENABLED)) {
                $enabled[] = $id;
            }
        }

        return $enabled;
    }

    /**
     * Returns an array of all available modules
     * 
     * @return ContentContainerModule[] a list of modules
     */
    public function getAvailable()
    {
        if ($this->_available !== null) {
            return $this->_available;
        }

        $this->_available = [];

        foreach (Yii::$app->moduleManager->getModules() as $id => $module) {
            if ($module instanceof ContentContainerModule && Yii::$app->hasModule($module->id) &&
                    $module->hasContentContainerType($this->contentContainer->className())) {
                $this->_available[$module->id] = $module;
            }
        }

        return $this->_available;
    }

    /**
     * Returns an array of all module states.
     * 
     * @see Module
     * @return array a list of modules with the corresponding state
     */
    protected function getStates()
    {
        $states = [];

        // Get states for this contentcontainer from database
        foreach (ContentContainerModuleState::findAll(['contentcontainer_id' => $this->contentContainer->contentcontainer_id]) as $module) {
            $states[$module->module_id] = $module->module_state;
        }

        // Get default states, when no state is stored
        foreach ($this->getAvailable() as $module) {
            if (!isset($states[$module->id])) {
                $states[$module->id] = self::getDefaultState($this->contentContainer->className(), $module->id);
            }
        }

        return $states;
    }

    /**
     * Sets the default state for a module based on the contentcontainer class
     * 
     * @param string $class the class name (e.g. Space or User)
     * @param string $id the module id
     * @param int $state the state
     */
    public static function setDefaultState($class, $id, $state)
    {
        $reflect = new ReflectionClass($class);
        Yii::$app->getModule($id)->settings->set('moduleManager.defaultState.' . $reflect->getShortName(), $state);
    }

    /**
     * Returns the default module state for a given contentcontainer class
     * 
     * @param string $class the class name (e.g. Space or User)
     * @param string $id the module id
     * @return int|null the default state or null when no default state is defined
     */
    public static function getDefaultState($class, $id)
    {
        $reflect = new ReflectionClass($class);
        $state = Yii::$app->getModule($id)->settings->get('moduleManager.defaultState.' . $reflect->getShortName());

        if ($state === null) {
            return null;
        } else {
            return (int) $state;
        }
    }

    /**
     * Returns an Module record instance for the given module id
     * 
     * @see Module
     * @param string $id the module id
     * @return Module the Module record instance
     */
    protected function getModuleStateRecord($id)
    {
        $moduleState = ContentContainerModuleState::findOne(['module_id' => $id, 'contentcontainer_id' => $this->contentContainer->contentcontainer_id]);
        if ($moduleState === null) {
            $moduleState = new ContentContainerModuleState;
            $moduleState->contentcontainer_id = $this->contentContainer->contentcontainer_id;
            $moduleState->module_id = $id;
        }

        return $moduleState;
    }

    /**
     * Returns a query for \humhub\modules\content\models\ContentContainer where the given module is enabled.
     * 
     * @param string $id the module mid
     * @return \yii\db\ActiveQuerythe list of content container
     */
    public static function getContentContainerQueryByModule($id)
    {
        $query = ContentContainer::find();

        $query->leftJoin('contentcontainer_module', 'contentcontainer_module.contentcontainer_id=contentcontainer.id AND contentcontainer_module.module_id=:moduleId', [':moduleId' => $id]);
        $query->andWhere(['contentcontainer_module.module_state' => ContentContainerModuleState::STATE_ENABLED]);
        $query->orWhere(['contentcontainer_module.module_state' => ContentContainerModuleState::STATE_FORCE_ENABLED]);

        $moduleSettings = Yii::$app->getModule($id)->settings;

        // Add default enabled modules
        $contentContainerClasses = [\humhub\modules\user\models\User::class, \humhub\modules\space\models\Space::class];
        foreach ($contentContainerClasses as $class) {
            $reflect = new ReflectionClass($class);
            $defaultState = (int) $moduleSettings->get('moduleManager.defaultState.' . $reflect->getShortName());
            if ($defaultState === ContentContainerModuleState::STATE_ENABLED || $defaultState === ContentContainerModuleState::STATE_FORCE_ENABLED) {
                $query->orWhere(['contentcontainer.class' => $class]);
            }
        }

        return $query;
    }

}
