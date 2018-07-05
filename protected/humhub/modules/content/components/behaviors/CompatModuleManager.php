<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components\behaviors;

use yii\base\Behavior;

/**
 * Compatibility layer for old ContentContainer ModuleManager calls.
 *
 * @see \humhub\modules\content\components\ContentContainerModuleManager
 * @see \humhub\modules\content\components\ContentContainerActiveRecord
 * @since 1.3
 * @author luke
 *
 * @property \humhub\modules\content\components\ContentContainerActiveRecord $owner
 */
class CompatModuleManager extends Behavior
{
    /**
     * @var \humhub\modules\content\components\ContentContainerModuleManager
     */
    public $moduleManager;

    public function attach($owner)
    {
        parent::attach($owner);
        $this->moduleManager = $this->owner->moduleManager;
    }

    public function getAvailableModules()
    {
        return $this->moduleManager->getAvailable();
    }

    public function getEnabledModules()
    {
        return $this->moduleManager->getEnabled();
    }

    public function isModuleEnabled($moduleId)
    {
        return $this->moduleManager->isEnabled($moduleId);
    }

    public function enableModule($moduleId)
    {
        return $this->moduleManager->enable($moduleId);
    }

    public function canDisableModule($id)
    {
        return $this->moduleManager->canDisable($id);
    }

    public function disableModule($moduleId)
    {
        return $this->moduleManager->disable($moduleId);
    }
}
