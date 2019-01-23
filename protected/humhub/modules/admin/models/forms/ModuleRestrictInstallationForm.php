<?php

namespace humhub\modules\admin\models\forms;
use humhub\modules\content\components\ContentContainerModuleManager;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

/**
 * GroupForm is used to modify group settings
 *
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class ModuleRestrictInstallationForm extends \yii\base\Model
{

    public $onlyForSpaces;
    public $onlyForProfiles;
    public $onlyForAdmins;

    /**
     * Validation rules for group form
     *
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['onlyForSpaces', 'onlyForProfiles', 'onlyForAdmins'], 'integer'],
        ];
    }

    public function initFormData($module)
    {
        $onlyForSpaces = ContentContainerModuleManager::getRestrictInstallationState(Space::class, $module->id);
        $onlyForProfiles = ContentContainerModuleManager::getRestrictInstallationState(User::class, $module->id);
        $availableForBoth = (! $onlyForSpaces && ! $onlyForProfiles);
        $this->onlyForSpaces = $availableForBoth ? $onlyForSpaces : ! $onlyForSpaces;
        $this->onlyForProfiles = $availableForBoth ? $onlyForProfiles : ! $onlyForProfiles;
        $this->onlyForAdmins = $module->settings->get('installation_only_for_admins');
    }

    public function saveFormData($module)
    {
        $availableForBoth = (! $this->onlyForSpaces && ! $this->onlyForProfiles);
        $onlyForSpaces = $availableForBoth ? $this->onlyForSpaces : ! $this->onlyForSpaces;
        $onlyForProfiles = $availableForBoth ? $this->onlyForProfiles : ! $this->onlyForProfiles;
        ContentContainerModuleManager::setRestrictInstallationState(Space::class, $module->id, $onlyForSpaces);
        ContentContainerModuleManager::setRestrictInstallationState(User::class, $module->id, $onlyForProfiles);
        $module->settings->set('installation_only_for_admins', $this->onlyForAdmins);
    }
}
