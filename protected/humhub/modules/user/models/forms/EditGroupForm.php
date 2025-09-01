<?php

namespace humhub\modules\user\models\forms;

use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupSpace;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\GroupUser;
use Yii;
use yii\db\Expression;

/**
 * Description of EditGroupForm
 *
 * @author buddha
 */
class EditGroupForm extends Group
{
    public const TYPE_NORMAL = 'normal';
    public const TYPE_SUBGROUP = 'subgroup';
    public string $type = self::TYPE_NORMAL;
    public $subgroups;
    public $parent;

    public $managerGuids = [];
    public $defaultSpaceGuid = [];
    public $updateSpaceMemberships = false;

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();

        $this->type = $this->parent_group_id === null ? self::TYPE_NORMAL : self::TYPE_SUBGROUP;

        switch ($this->type) {
            case self::TYPE_NORMAL:
                $this->subgroups = Group::find()
                    ->where(['parent_group_id' => $this->id])
                    ->select('id')
                    ->column();
                break;
            case self::TYPE_SUBGROUP:
                $this->parent = [$this->parent_group_id];
                break;
        }
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['name', 'type'], 'required'];
        $rules[] = [['updateSpaceMemberships'], 'boolean'];
        $rules[] = [['managerGuids', 'show_at_registration', 'show_at_directory', 'defaultSpaceGuid'], 'safe'];
        $rules[] = [['subgroups', 'parent'], 'safe'];
        $rules[] = [['type'], 'validateTypeGroups'];
        return $rules;
    }

    public function validateTypeGroups()
    {
        if ($this->type === self::TYPE_SUBGROUP) {
            if (empty($this->parent[0])) {
                $this->addError('parent', Yii::t('AdminModule.user', 'Parent group is required!'));
                return;
            }
            $parentIsSubGroup = Group::find()
                ->where(['id' => $this->parent[0]])
                ->andWhere(['IS NOT', 'parent_group_id', new Expression('NULL') ]);
            if ($parentIsSubGroup->exists()) {
                $this->addError('parent', 'Parent group cannot be a subgroup!');
                return;
            }
        }

        if ($this->isNewRecord) {
            return;
        }

        if (is_array($this->subgroups) && in_array($this->id, $this->subgroups)) {
            $this->addError('subgroups', 'Subgroup cannot be same current group!');
        }

        if (is_array($this->parent) && in_array($this->id, $this->parent)) {
            $this->addError('parent', 'Parent group cannot be same current group!');
        }
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'defaultSpaceGuid' => Yii::t('AdminModule.space', 'Default Space(s)'),
            'updateSpaceMemberships' => Yii::t('AdminModule.space', 'Update Space memberships also for existing members.'),
            'type' => Yii::t('AdminModule.user', 'Group Type'),
            'subgroups' => Yii::t('AdminModule.user', 'Subgroup(s)'),
            'parent' => Yii::t('AdminModule.user', 'Parent Group'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (!$this->managerGuids) {
            $this->managerGuids = [];
        }

        $this->addNewManagers();
        $this->removeOldManagers();

        $existingSpaceIds = GroupSpace::find()->where(['group_id' => $this->id])->select('space_id')->column();
        $newSpaceIds = [];
        if (is_array($this->defaultSpaceGuid)) {
            foreach ($this->defaultSpaceGuid as $spaceGuid) {
                $space = Space::findOne(['guid' => $spaceGuid]);
                if ($space !== null) {
                    $newSpaceIds[] = $space->id;
                }
            }
        }

        foreach (array_diff($existingSpaceIds, $newSpaceIds) as $spaceId) {
            GroupSpace::deleteAll(['space_id' => $spaceId, 'group_id' => $this->id]);
        }

        foreach (array_diff($newSpaceIds, $existingSpaceIds) as $spaceId) {
            $groupSpaces = new GroupSpace();
            $groupSpaces->group_id = $this->id;
            $groupSpaces->space_id = $spaceId;
            $groupSpaces->save();
        }

        Group::updateAll(['parent_group_id' => null], ['parent_group_id' => $this->id]);
        switch ($this->type) {
            case self::TYPE_NORMAL:
                $this->updateAttributes(['parent_group_id' => null]);
                if (is_array($this->subgroups) && $this->subgroups !== []) {
                    Group::updateAll(['parent_group_id' => $this->id], ['id' => $this->subgroups]);
                }
                break;
            case self::TYPE_SUBGROUP:
                $this->updateAttributes(['parent_group_id' => $this->parent[0] ?? null]);
                break;
        }

        parent::afterSave($insert, $changedAttributes);
    }

    protected function addNewManagers()
    {
        $managers = User::find()->where(['guid' => $this->managerGuids])->all();
        foreach ($managers as $manager) {
            $groupUser = GroupUser::findOne(['group_id' => $this->id, 'user_id' => $manager->id]);
            if ($groupUser != null && !$groupUser->is_group_manager) {
                $groupUser->is_group_manager = true;
                $groupUser->save();
            } else {
                $this->addUser($manager, true);
            }
        }
    }

    protected function removeOldManagers()
    {
        //Remove admins not contained in the selection
        foreach ($this->getManager()->all() as $manager) {
            if (!in_array($manager->guid, $this->managerGuids)) {
                $groupUser = GroupUser::findOne(['group_id' => $this->id, 'user_id' => $manager->id]);
                if ($groupUser != null) {
                    $groupUser->is_group_manager = false;
                    $groupUser->save();
                }
            }
        }
    }

    public function getTypeOptions(): array
    {
        return [
            self::TYPE_NORMAL => Yii::t('AdminModule.user', 'Normal'),
            self::TYPE_SUBGROUP => Yii::t('AdminModule.user', 'Subgroup'),
        ];
    }
}
