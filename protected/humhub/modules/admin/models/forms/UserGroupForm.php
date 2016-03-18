<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\modules\user\models\Group;

/**
 * Description of UserGroupForm
 *
 * @author buddha
 */
class UserGroupForm extends \yii\base\Model
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
     * User model object
     * @var type 
     */
    public $user;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['groupSelection', 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'groupSelection' => 'Groups'
        ];
    }

    /**
     * Sets the user data and intitializes the from selection
     * @param type $user
     */
    public function setUser($user)
    {
        //Set form user and current group settings
        $this->user = $user;
        $this->currentGroups = $user->groups;

        //Set the current group selection
        $this->groupSelection = [];
        foreach ($this->currentGroups as $group) {
            $this->groupSelection[] = $group->id;
        }
    }

    /**
     * Aligns the given group selection with the db
     * @return boolean
     */
    public function save()
    {
        //Check old group selection and remove non selected groups
        foreach($this->currentGroups as $userGroup) {
            if(!$this->isInGroupSelection($userGroup)) {
                $this->user->getGroupUsers()->where(['group_id' => $userGroup->id])->one()->delete();
            }
        }
        
        //Add all selectedGroups to the given user
        foreach ($this->groupSelection as $groupId) {
            if (!$this->isCurrentlyMemberOf($groupId)) {
                Group::findOne($groupId)->addUser($this->user);
            }
        }
        return true;
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
    public static function getGroupItems($groups)
    {
        $result = [];
        foreach ($groups as $group) {
            $result[$group->id] = $group->name;
        }
        return $result;
    }

}
