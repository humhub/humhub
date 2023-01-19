<?php

namespace humhub\modules\admin\models\forms;

use humhub\modules\content\components\ContentContainerModuleManager;
use humhub\modules\content\models\ContentContainerModuleState;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;

/**
 * GroupForm is used to modify group settings
 *
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class ModuleSetAsDefaultForm extends Model
{
    /** @var string */
    protected $moduleId;

    /** @var int | string */
    public $spaceDefaultState;

    /** @var int | string */
    public $userDefaultState;

    /**
     * Validation rules for group form
     *
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['userDefaultState', 'spaceDefaultState'], 'in', 'range' => ContentContainerModuleState::getStates()],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'spaceDefaultState' => Yii::t('AdminModule.modules', 'Space default state'),
            'userDefaultState' => Yii::t('AdminModule.modules', 'User default state')
        ];
    }

    /**
     * @return array
     */
    public function getStatesList()
    {
        return ContentContainerModuleState::getStates(true);
    }

    /**
     * @param $id
     * @return $this
     */
    public function setModule($id): self
    {
        if ($this->moduleId == $id) {
            return $this;
        }

        $this->moduleId = $id;
        $this->spaceDefaultState = ContentContainerModuleManager::getDefaultState(Space::class, $id) ?? ContentContainerModuleState::STATE_DISABLED;
        $this->userDefaultState = ContentContainerModuleManager::getDefaultState(User::class, $id) ?? ContentContainerModuleState::STATE_DISABLED;

        return $this;
    }

    /**
     * @return bool
     */
    public function save($validate = true)
    {
        if ($validate && !$this->validate()) {
            return false;
        }

        ContentContainerModuleManager::setDefaultState(User::class, $this->moduleId, $this->userDefaultState);
        ContentContainerModuleManager::setDefaultState(Space::class, $this->moduleId, $this->spaceDefaultState);

        return true;
    }
}
