<?php

namespace humhub\modules\admin\models\forms;

use Yii;
use humhub\modules\user\models\Group;

/**
 * Description of UserEditForm
 *
 * @author buddha
 */
class UserEditForm extends \humhub\modules\user\models\User
{
    /**
     * GroupId selection array of the form.
     * @var type
     */
    public $groupSelection;

    /**
     * Current member groups (models) of the given $user
     * @var type
     *
     */
    public $currentGroups;

    /**
     * @inheritdoc
     */
    public function initGroupSelection()
    {
        $this->currentGroups = $this->groups;

        //Set the current group selection
        $this->groupSelection = [];
        foreach ($this->currentGroups as $group) {
            $this->groupSelection[] = $group->id;
        }

        parent::init();
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['editAdmin'][] = 'groupSelection';

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), ['groupSelection' => 'Groups']);
    }

    /**
     * Aligns the given group selection with the db
     * @return boolean
     */
    public function afterSave($insert, $changedAttributes)
    {
        if(Yii::$app->user->can(new \humhub\modules\admin\permissions\ManageGroups())) {
            //Check old group selection and remove non selected groups
            foreach($this->currentGroups as $userGroup) {
                if(!$this->isInGroupSelection($userGroup)) {
                    $this->getGroupUsers()->where(['group_id' => $userGroup->id])->one()->delete();
                }
            }

            $this->groupSelection = ($this->groupSelection == null) ? [] : $this->groupSelection;

            //Add all new selectedGroups to the given user
            foreach ($this->groupSelection as $groupId) {
                if (!$this->isCurrentlyMemberOf($groupId)) {
                    Group::findOne($groupId)->addUser($this);
                }
            }
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Checks if the given group (id or model object) is contained in the form selection
     * @param type $groupId groupId or Group model object
     * @return boolean true if contained in selection else false
     */
    private function isInGroupSelection($groupId)
    {
        $groupId = ($groupId instanceof Group) ? $groupId->id : $groupId;
        $this->groupSelection = (is_array($this->groupSelection)) ? $this->groupSelection : [];

        return is_array($this->groupSelection) && in_array($groupId, $this->groupSelection);
    }

    /**
     * Checks if the user is member of the given group (id or model object)
     * @param type $groupId $groupId groupId or Group model object
     * @return boolean true if user is member else false
     */
    private function isCurrentlyMemberOf($groupId)
    {
        $groupId = ($groupId instanceof Group) ? $groupId->id : $groupId;
        foreach ($this->currentGroups as $userGroup) {
            if ($userGroup->id === $groupId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns an id => groupname array representation of the given $groups array.
     * @param array $groups array of Group models
     * @return type array in form of id => groupname
     */
    public static function getGroupItems($groups = null)
    {
        if($groups == null) {
            $groups = \humhub\modules\user\models\Group::find()->all();
        }

        $result = [];
        foreach ($groups as $group) {
            $result[$group->id] = $group->name;
        }

        return $result;
    }
}
