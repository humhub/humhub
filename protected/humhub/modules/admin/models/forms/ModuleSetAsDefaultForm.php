<?php

namespace humhub\modules\admin\models\forms;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\content\components\ContentContainerModuleManager;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\content\models\ContentContainerModuleState;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\db\IntegrityException;

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
     * @var bool
     */
    public $moduleDeactivationConfirmed = false;

    /**
     * Validation rules for group form
     *
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['userDefaultState', 'spaceDefaultState'], 'in', 'range' => ContentContainerModuleState::getStates()],
            [['moduleDeactivationConfirmed'], 'boolean'],
            [['moduleDeactivationConfirmed'], function ($attribute, $params, $validator) {
                if ($this->mustConfirmModuleDeactivation()) {
                    $this->addError($attribute, '');
                }
            }],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'spaceDefaultState' => Yii::t('AdminModule.modules', 'Space default state'),
            'userDefaultState' => Yii::t('AdminModule.modules', 'User default state'),
            'moduleDeactivationConfirmed' => Yii::t('AdminModule.modules', 'The module is currently being used by {nbContainers} users or spaces. If you change its availability, all content created with the module will be lost. Proceed?', ['nbContainers' => count($this->getModuleDeactivationContainers())]),
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
     * @throws IntegrityException
     * @throws Exception
     */
    public function save($validate = true)
    {
        if ($validate && !$this->validate()) {
            return false;
        }

        // Disable module for users and spaces
        foreach ($this->getModuleDeactivationContainers() as $container) {
            $contentContainerActiveRecord = $container->getPolymorphicRelation();
            if ($contentContainerActiveRecord instanceof ContentContainerActiveRecord) {
                $contentContainerActiveRecord->moduleManager->disable($this->moduleId);
            }
        }

        ContentContainerModuleManager::setDefaultState(User::class, $this->moduleId, $this->userDefaultState);
        ContentContainerModuleManager::setDefaultState(Space::class, $this->moduleId, $this->spaceDefaultState);

        return true;
    }

    /**
     * Get the content containers where the module is to be disabled
     * @return array|ContentContainer[]
     */
    public function getModuleDeactivationContainers(): array
    {
        $module = Yii::$app->getModule($this->moduleId);
        if (!$module instanceof ContentContainerModule) {
            return [];
        }

        if (
            (int)$this->userDefaultState === ContentContainerModuleState::STATE_NOT_AVAILABLE
            && (int)$this->spaceDefaultState === ContentContainerModuleState::STATE_NOT_AVAILABLE
        ) {
            return $module->getEnabledContentContainers();
        }

        if ((int)$this->userDefaultState === ContentContainerModuleState::STATE_NOT_AVAILABLE) {
            return $module->getEnabledContentContainers(User::class);
        }

        if ((int)$this->spaceDefaultState === ContentContainerModuleState::STATE_NOT_AVAILABLE) {
            return $module->getEnabledContentContainers(Space::class);
        }

        return [];
    }

    public function mustConfirmModuleDeactivation(): bool
    {
        return
            !$this->moduleDeactivationConfirmed
            && $this->getModuleDeactivationContainers();
    }
}
