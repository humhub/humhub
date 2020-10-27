<?php
namespace humhub\modules\user\models\forms;

use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupSpaces;
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

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['name'], 'required'];
        $rules[] = [['managerGuids', 'show_at_registration', 'show_at_directory', 'defaultSpaceGuid'], 'safe'];
        return $rules;
    }

    public function attributeLabels()
    {
        return [
            'defaultSpaceGuid' => 'Default Space(s)',
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

        //clear GroupSpaces
        $group_spaces = GroupSpaces::find()->where(['group_id'=>$this->id])->all();
        foreach ($group_spaces as $group_space){
            $group_space->delete();
        }

        // Save GroupSpaces for this group
        if (!empty($this->defaultSpaceGuid)) {
            foreach ($this->defaultSpaceGuid as $space_guid){
                $space = Space::findOne(['guid' => $space_guid]);
                $group_spaces = new GroupSpaces();
                $group_spaces->group_id = $this->id;
                $group_spaces->space_id = $space->id;
                $group_spaces->save();
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
