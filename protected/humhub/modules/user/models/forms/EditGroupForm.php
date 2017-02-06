<?php
namespace humhub\modules\user\models\forms;

use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\GroupUser;

/**
 * Description of EditGroupForm
 *
 * @author buddha
 */
class EditGroupForm extends \humhub\modules\user\models\Group
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

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // When on edit form scenario, save also defaultSpaceGuid/managerGuids
        if (empty($this->defaultSpaceGuid)) {
            $this->space_id = null;
        } else {
            $space = Space::findOne(['guid' => $this->defaultSpaceGuid[0]]);
            if ($space !== null) {
                $this->space_id = $space->id;
            }
        }

        return parent::beforeSave($insert);
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
