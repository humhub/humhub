<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\modules\content\models\ContentContainer;
use humhub\modules\content\models\ContentContainerModuleState;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use ReflectionClass;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;

/**
 * ModuleManager handles modules of a content container.
 *
 * @since 1.3
 * @author Luke
 */
class ContentContainerModuleManager extends Component
{
    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    /**
     * @var array the available module ids
     */
    private $_available;

    /**
     * @var array the cached states per module
     */
    private $_states;

    /**
     * Disables a module for the content container
     *
     * @param string $id the module id
     * @return bool
     * @throws Exception
     */
    public function disable($id)
    {
        if ($this->canDisable($id)) {
            Yii::$app->moduleManager->getModule($id)->disableContentContainer($this->contentContainer);

            $moduleState = $this->getModuleStateRecord($id);
            if ($moduleState instanceof ContentContainerModuleState) {
                $moduleState->module_state = ContentContainerModuleState::STATE_DISABLED;
                return $moduleState->save();
            }
        }

        return false;
    }

    /**
     * Enables a module for this content container
     *
     * @param string $id the module id
     * @return bool
     * @throws Exception
     */
    public function enable($id)
    {
        if ($this->canEnable($id)) {
            Yii::$app->moduleManager->getModule($id)->enableContentContainer($this->contentContainer);

            $moduleState = $this->getModuleStateRecord($id);
            if ($moduleState instanceof ContentContainerModuleState) {
                $moduleState->module_state = ContentContainerModuleState::STATE_ENABLED;
                return $moduleState->save();
            }
        }

        return false;
    }

    /**
     * Checks whether the module is activated or not
     *
     * @param string $id the module id
     * @return bool
     */
    public function isEnabled($id)
    {
        // Workaround for core post module
        if ($id === 'post') {
            return true;
        }

        return in_array($id, $this->getEnabled());
    }

    /**
     * Checks whether the module can be enabled or not
     *
     * @param string $id the module id
     * @return bool
     * @throws Exception
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
     * @return bool
     */
    public function canDisable($id)
    {
        if (!$this->isEnabled($id) || self::getDefaultState(get_class($this->contentContainer), $id) === ContentContainerModuleState::STATE_FORCE_ENABLED) {
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
        $enabled[] = 'post';

        return $enabled;
    }

    /**
     * Returns an array of all available modules
     *
     * @return ContentContainerModule[] a list of modules
     * @throws Exception
     */
    public function getAvailable()
    {
        if ($this->_available !== null) {
            return $this->_available;
        }

        $this->_available = [];

        foreach (Yii::$app->moduleManager->getModules() as $module) {
            if ($this->isAvailableModule($module)) {
                $this->_available[$module->id] = $module;
            }
        }

        return $this->_available;
    }

    public function isAvailableModule($module): bool
    {
        return
            $module instanceof ContentContainerModule
            && $module->getIsEnabled()
            && $module->hasContentContainerType(get_class($this->contentContainer))
            && self::getDefaultState(get_class($this->contentContainer), $module->id) !== ContentContainerModuleState::STATE_NOT_AVAILABLE;
    }

    /**
     * Returns a list of modules that can be installed by the ContentContainer.
     * Unlike `getAvailable()` it does not contain any modules which cannot be disabled or enabled.
     *
     * @return ContentContainerModule[] a list of modules
     * @throws Exception
     * @since 1.6.5
     */
    public function getInstallable()
    {

        $availableModules = $this->getAvailable();
        foreach ($availableModules as $moduleId => $module) {
            if (($this->isEnabled($moduleId) && !$this->canDisable($moduleId))
                || (!$this->isEnabled($moduleId) && !$this->canEnable($moduleId))
            ) {
                unset($availableModules[$moduleId]);
            }
        }
        return $availableModules;

    }

    /**
     * Flushes the cache of available modules.
     * @since 1.3.11
     */
    public function flushCache()
    {
        $this->_available = null;
        $this->_states = null;
    }

    /**
     * Returns an array of all module states.
     *
     * @return array a list of modules with the corresponding state
     * @see Module
     */
    protected function getStates()
    {
        if (isset($this->_states)) {
            return $this->_states;
        }

        $this->_states = [];

        // Get states for this contentcontainer from database
        foreach (ContentContainerModuleState::findAll(['contentcontainer_id' => $this->contentContainer->contentcontainer_id]) as $module) {
            $this->_states[$module->module_id] = $module->module_state;
        }

        // Get default states, when no state is stored
        foreach ($this->getAvailable() as $module) {
            if (!isset($this->_states[$module->id])) {
                $this->_states[$module->id] = self::getDefaultState(get_class($this->contentContainer), $module->id);
            }
        }

        return $this->_states;
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
            return (int)$state;
        }
    }

    /**
     * Returns an Module record instance for the given module id
     *
     * @param string $id the module id
     * @return ContentContainerModuleState|null
     * @see Module
     */
    protected function getModuleStateRecord($id)
    {
        if (!$this->contentContainer->contentContainerRecord instanceof ContentContainer) {
            return null;
        }

        $moduleState = ContentContainerModuleState::findOne([
            'module_id' => $id,
            'contentcontainer_id' => $this->contentContainer->contentcontainer_id,
        ]);

        if ($moduleState === null) {
            $moduleState = new ContentContainerModuleState();
            $moduleState->contentcontainer_id = $this->contentContainer->contentcontainer_id;
            $moduleState->module_id = $id;
        }

        return $moduleState;
    }

    /**
     * Returns a query for \humhub\modules\content\models\ContentContainer where the given module is enabled.
     *
     * @param string $id the module mid
     * @return ActiveQuery the list of content container
     */
    public static function getContentContainerQueryByModule($id)
    {
        $query = ContentContainer::find();

        $query->leftJoin('contentcontainer_module', 'contentcontainer_module.contentcontainer_id=contentcontainer.id AND contentcontainer_module.module_id=:moduleId', [':moduleId' => $id]);
        $query->andWhere(['contentcontainer_module.module_state' => ContentContainerModuleState::STATE_ENABLED]);
        $query->orWhere(['contentcontainer_module.module_state' => ContentContainerModuleState::STATE_FORCE_ENABLED]);

        $moduleSettings = Yii::$app->getModule($id)->settings;

        // Add default enabled modules
        $contentContainerClasses = [User::class, Space::class];
        foreach ($contentContainerClasses as $class) {
            $reflect = new ReflectionClass($class);
            $defaultState = (int)$moduleSettings->get('moduleManager.defaultState.' . $reflect->getShortName());
            if ($defaultState === ContentContainerModuleState::STATE_ENABLED || $defaultState === ContentContainerModuleState::STATE_FORCE_ENABLED) {
                $query->orWhere(['contentcontainer.class' => $class]);
            }
        }

        return $query;
    }

    /**
     * This method is called to determine classes of Content models which can be posted on wall.
     *
     * @return ContentActiveRecord[]
     * @since 1.13
     */
    public function getContentClasses(): array
    {
        return Yii::$app->runtimeCache->getOrSet(__METHOD__ . $this->contentContainer->contentcontainer_id, function () {
            $contentClasses = [];
            foreach ($this->getEnabled() as $moduleId) {
                $module = Yii::$app->getModule($moduleId);
                foreach ($module->getContentClasses($this->contentContainer) as $class) {
                    $content = new $class($this->contentContainer);
                    if (!($content instanceof ContentActiveRecord)) {
                        throw new InvalidConfigException($class . ' must be instance of ContentActiveRecord!');
                    }
                    $contentClasses[] = $content;
                }
            }

            return $contentClasses;
        });
    }

}
