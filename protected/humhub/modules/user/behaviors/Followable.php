<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\behaviors;

use humhub\modules\space\models\Space;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Behavior;

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
        UserFollow::model()->deleteAllByAttributes(['object_model' => get_class($this->getOwner()), 'object_id' => $this->getOwner()->getPrimaryKey()]);
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
        if ($userId instanceof User) {
            $userId = $userId->id;
        } elseif (!$userId || $userId == "") {
            $userId = Yii::$app->user->id;
        }

        // User cannot follow himself
        if ($this->owner instanceof User && $this->owner->id == $userId) {
            return false;
        } elseif ($this->owner instanceof Space && $this->owner->isMember($userId)) {
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
        if ($userId instanceof User) {
            $userId = $userId->id;
        } elseif (!$userId || $userId == "") {
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
        if ($userId instanceof User) {
            $userId = $userId->id;
        } elseif (!$userId || $userId == "") {
            $userId = \Yii::$app->user->id;
        }

        if (!isset($this->_followerCache[$userId])) {
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
     * Get a query of users which are followers of this object.
     *
     * @param ActiveQueryUser $query e.g. for limit the result
     * @return ActiveQueryUser
     */
    public function getFollowersQuery($query = null)
    {
        if ($query === null) {
            $query = User::find();
        }

        return $query->visible()
            ->leftJoin('user_follow', 'user.id = user_follow.user_id AND user_follow.object_id=:object_id AND user_follow.object_model = :object_model', [
                ':object_model' => get_class($this->owner),
                ':object_id' => $this->owner->getPrimaryKey(),
            ])
            ->andWhere('user_follow.user_id IS NOT null');
    }

    /**
     * Returns the number of users which are followers of this object.
     *
     * @return int
     */
    public function getFollowerCount()
    {
        return $this->getFollowersQuery()->count();
    }

    /**
     * Returns an array of users which are followers of this object.
     *
     * @param ActiveQueryUser $query e.g. for limit the result
     * @param boolean $withNotifications only return followers with enabled notifications
     * @param boolean $returnQuery only return the query instead of User objects
     * @return User[]|ActiveQueryUser the user objects or the active query
     */
    public function getFollowers($query = null, $withNotification = false, $returnQuery = false)
    {
        $query = $this->getFollowersQuery($query);

        if ($withNotification) {
            $query->andWhere('user_follow.send_notifications=1');
        }

        if ($returnQuery) {
            return $query;
        }

        return $query->all();
    }

    /**
     * Get a query of objects which the owner object follows
     *
     * @param ActiveQueryUser $query e.g. for limit the result
     * @return ActiveQueryUser
     */
    public function getFollowingQuery($query = null)
    {
        if ($query === null) {
            $query = User::find();
        }

        return $query->visible()
            ->leftJoin('user_follow', 'user.id=user_follow.object_id AND user_follow.object_model=:object_model', ['object_model' => get_class($this->owner)])
            ->andWhere(['user_follow.user_id' => $this->owner->id]);
    }

    /**
     * Returns the number of follows the owner object performed.
     * This is only affects User owner objects!
     *
     * @param string $objectModel DEPRECATED HActiveRecord Classname to restrict Object Classes to (e.g. User)
     * @return int
     */
    public function getFollowingCount($objectModel = null)
    {
        return $this->getFollowingQuery()->count();
    }

    /**
     * Returns an array of object which the owner object follows.
     * This is only affects User owner objects!
     *
     * E.g. Get list of spaces which are the user follows.
     *
     * @param ActiveQueryUser $query e.g. for limit the result
     * @param string $objectModel HActiveRecord Classname to restrict Object Classes to (e.g. User)
     * @return User[]
     */
    public function getFollowingObjects($query)
    {
        return $this->getFollowingQuery($query)->all();
    }

}
