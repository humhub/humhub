<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * HFollowableBehavior adds following methods to HActiveRecords
 *
 * @author Lucas Bartholemy <lucas.bartholemy@humhub.com>
 * @package humhub.modules_core.user.behaviors
 * @since 0.5
 */
class HFollowableBehavior extends CActiveRecordBehavior
{

    public function beforeDelete($event)
    {
        UserFollow::model()->deleteAllByAttributes(array('object_model' => get_class($this->getOwner()), 'object_id' => $this->getOwner()->getPrimaryKey()));
        return parent::beforeValidate($event);
    }

    /**
     * Return the follow record based on the owner record and the given user id
     * 
     * @param int $userId
     * @return Follow
     */
    private function getFollowRecord($userId)
    {
        return UserFollow::model()->findByAttributes(array('object_model' => get_class($this->getOwner()), 'object_id' => $this->getOwner()->getPrimaryKey(), 'user_id' => $userId));
    }

    /**
     * Follows the owner object
     * 
     * @param int $userId
     * @return boolean 
     */
    public function follow($userId = "", $withNotifications = true)
    {
        if ($userId == "")
            $userId = Yii::app()->user->id;

        // User cannot follow himself
        if (get_class($this->getOwner()) == 'User' && $this->getOwner()->getPrimaryKey() == $userId) {
            return false;
        }

        $follow = $this->getFollowRecord($userId);
        if ($follow === null) {
            $follow = new UserFollow();
            $follow->user_id = $userId;
            $follow->object_id = $this->getOwner()->getPrimaryKey();
            $follow->object_model = get_class($this->getOwner());
        }

        if ($withNotifications) {
            $follow->send_notifications = 1;
        } else {
            $follow->send_notifications = 0;
        }

        if (!$follow->save()) {
            return false;
        }
        return true;
    }

    /**
     * Unfollows the owner object
     * 
     * @param int $userId
     * @return boolean
     */
    public function unfollow($userId = "")
    {
        if ($userId == "")
            $userId = Yii::app()->user->id;

        $record = $this->getFollowRecord($userId);
        if ($record !== null) {
            if ($record->delete()) {
                return true;
            }
        } else {
            // Not follow this object
            return false;
        }

        return false;
    }

    /**
     * Checks if the given user follows this owner record.
     * 
     * @param int $userId
     * @param boolean $withNotifcations if true, only return true when also notifications enabled
     * @return boolean Is object followed by user
     */
    public function isFollowedByUser($userId = "", $withNotifications = false)
    {
        if ($userId == "") {
            $userId = Yii::app()->user->id;
        }

        $record = $this->getFollowRecord($userId);
        if ($record !== null) {
            if ($withNotifications && $record->send_notifications == 1) {
                return true;
            } elseif (!$withNotifications) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the number of users which are followers of this object.
     * 
     * @return int
     */
    public function getFollowerCount()
    {
        return UserFollow::model()->countByAttributes(array('object_id' => $this->getOwner()->getPrimaryKey(), 'object_model' => get_class($this->getOwner())));
    }

    /**
     * Returns an array of users which are followers of this object.
     * 
     * @param CDbCriteria $eCriteria e.g. for limit the result
     * @param boolean $withNotifications only return followers with enabled notifications
     * @return Array of Users
     */
    public function getFollowers($eCriteria = null, $withNotification=false)
    {
        $criteria = new CDbCriteria();
        $criteria->join = 'LEFT JOIN user_follow ON t.id = user_follow.user_id AND user_follow.object_id=:object_id AND user_follow.object_model=:object_model';
        $criteria->condition = 'user_follow.user_id IS NOT NULL';
        $criteria->params = array(':object_id' => $this->getOwner()->getPrimaryKey(), ':object_model' => get_class($this->getOwner()));
        
        if ($withNotification) {
            $criteria->condition .= ' AND user_follow.send_notifications=1';
        }
        
        
        if ($eCriteria !== null) {
            $criteria->mergeWith($eCriteria);
        }

        return User::model()->findAll($criteria);
    }

    /**
     * Returns the number of follows the owner object performed.
     * This is only affects User owner objects!
     * 
     * @param string $objectModel HActiveRecord Classname to restrict Object Classes to (e.g. User)
     * @return int
     */
    public function getFollowingCount($objectModel)
    {
        if (!class_exists($objectModel)) {
            throw new CException("Invalid objectModel parameter given!");
        }

        return UserFollow::model()->countByAttributes(array('user_id' => $this->getOwner()->getPrimaryKey(), 'object_model' => $objectModel));
    }

    /**
     * Returns an array of object which the owner object follows.
     * This is only affects User owner objects!
     * 
     * E.g. Get list of spaces which are the user follows.
     * 
     * @param CDbCriteria $eCriteria e.g. for limit the result
     * @param string $objectModel HActiveRecord Classname to restrict Object Classes to (e.g. User)
     * @return Array
     */
    public function getFollowingObjects($objectModel, $eCriteria = null)
    {
        $userId = $this->getOwner()->getPrimaryKey();

        if (!class_exists($objectModel)) {
            throw new CException("Invalid objectModel parameter given!");
        }

        $criteria = new CDbCriteria();
        $criteria->join = 'LEFT JOIN user_follow ON t.id = user_follow.object_id AND user_follow.object_model=:object_model';
        $criteria->condition = 'user_follow.user_id=:user_id';
        $criteria->params = array(':user_id' => $userId, ':object_model' => get_class($this->getOwner()));

        if ($eCriteria !== null) {
            $criteria->mergeWith($eCriteria);
        }

        return $objectModel::model()->findAll($criteria);
    }

}
