<?php

namespace humhub\modules\user\models\forms;

use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupSpace;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\GroupUser;
use Yii;

/**
 * Description of EditGroupForm
 *
 * @author buddha
 */
class EditGroupForm extends Group
{

    public $managerGuids = [];
    public $defaultSpaceGuid = [];
    public $updateSpaceMemberships = false;

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['name'], 'required'];
        $rules[] = [['updateSpaceMemberships'], 'boolean'];
        $rules[] = [['managerGuids', 'show_at_registration', 'show_at_directory', 'defaultSpaceGuid'], 'safe'];
        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'defaultSpaceGuid' => Yii::t('AdminModule.space', 'Default Space(s)'),
            'updateSpaceMemberships'  => Yii::t('AdminModule.space', 'Update Space memberships also for existing members.'),
        ];
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

        //clear GroupSpace
        $groupSpaces = GroupSpace::find()->where(['group_id' => $this->id])->all();
        foreach ($groupSpaces as $groupSpace) {
            $groupSpace->delete();
        }

        // Save GroupSpace for this group
        if (!empty($this->defaultSpaceGuid)) {
            foreach ($this->defaultSpaceGuid as $spaceGuid) {
                $space = Space::findOne(['guid' => $spaceGuid]);
                $groupSpaces = new GroupSpace();
                $groupSpaces->group_id = $this->id;
                $groupSpaces->space_id = $space->id;
                $groupSpaces->save();
            }
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
}
