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

namespace humhub\modules\user\behaviors;

use Yii;
use yii\base\Behavior;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;

/**
 * HFollowableBehavior adds following methods to HActiveRecords
 *
 * @author Lucas Bartholemy <lucas.bartholemy@humhub.com>
 * @package humhub.modules_core.user.behaviors
 * @since 0.5
 */
class Followable extends Behavior
{
    
    private $_followerCache = [];

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
    public function getFollowRecord($userId)
    {
        $userId = ($userId instanceof User) ? $userId->id : $userId;
        return Follow::find()->where(['object_model' => $this->owner->className(), 'object_id' => $this->owner->getPrimaryKey(), 'user_id' => $userId])->one();
    }

    /**
     * Follows the owner object
     *
     * @param int $userId
     * @param boolean $withNotifications (since 1.2) sets the send_notifications setting of the membership default true
     * @return boolean
     */
    public function follow($userId = null, $withNotifications = true)
    {
        if($userId instanceof User) {
            $userId = $userId->id;
        } else if (!$userId || $userId == "") {
            $userId = Yii::$app->user->id;
        }

        // User cannot follow himself
        if ($this->owner instanceof User && $this->owner->id == $userId) {
            return false;
        }

        $follow = $this->getFollowRecord($userId);
        if ($follow === null) {
            $follow = new Follow(['user_id' => $userId]);
            $follow->setPolyMorphicRelation($this->owner);
        }

        $follow->send_notifications = $withNotifications;

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
    public function unfollow($userId = null)
    {
        if($userId instanceof User) {
            $userId = $userId->id;
        } else if (!$userId || $userId == "") {
            $userId = Yii::$app->user->id;
        }

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
     * Note that the followers for this owner will be cached.
     *
     * @param int $userId
     * @param boolean $withNotifications if true, only return true when also notifications enabled
     * @return boolean Is object followed by user
     */
    public function isFollowedByUser($userId = null, $withNotifications = false)
    {
        if($userId instanceof User) {
            $userId = $userId->id;
        } else if (!$userId || $userId == "") {
            $userId = \Yii::$app->user->id;
        }

        if(!isset($this->_followerCache[$userId])) {
            $this->_followerCache[$userId] = $this->getFollowRecord($userId);
        }
        
        $record = $this->_followerCache[$userId];
        
        if ($record) {
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
        return Follow::find()->where(['object_id' => $this->owner->getPrimaryKey(), 'object_model' => $this->owner->className()])->count();
    }

    /**
     * Returns an array of users which are followers of this object.
     *
     * @param CDbCriteria $eCriteria e.g. for limit the result
     * @param boolean $withNotifications only return followers with enabled notifications
     * @return Array of Users
     */
    public function getFollowers($query = null, $withNotification = false, $returnQuery = false)
    {

        if ($query === null) {
            $query = \humhub\modules\user\models\User::find();
        }

        $query->leftJoin('user_follow', 'user.id = user_follow.user_id AND user_follow.object_id=:object_id AND user_follow.object_model = :object_model', [
            ':object_model' => $this->owner->className(),
            ':object_id' => $this->owner->getPrimaryKey(),
        ]);

        $query->andWhere('user_follow.user_id IS NOT null');

        if ($withNotification) {
            $query->andWhere('user_follow.send_notifications=1');
        }

        if ($returnQuery) {
            return $query;
        }

        return $query->all();
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
        #if (!class_exists($objectModel)) {
        #    throw new CException("Invalid objectModel parameter given!");
        #}

        return Follow::find()->where(['user_id' => $this->owner->getPrimaryKey(), 'object_model' => $objectModel])->count();
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
    public function getFollowingObjects($query)
    {
        $query->leftJoin('user_follow', 'user.id=user_follow.object_id AND user_follow.object_model=:object_model', ['object_model' => $this->owner->className()]);
        $query->andWhere(['user_follow.user_id' => $this->owner->id]);

        return $query->all();
    }

}
