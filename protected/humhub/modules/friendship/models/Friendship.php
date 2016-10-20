<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\models;


use humhub\modules\user\models\User;

/**
 * This is the model class for table "user_friendship".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $friend_user_id
 * @property string $created_at
 *
 * @property User $friendUser
 * @property User $user
 */
class Friendship extends \humhub\components\ActiveRecord
{

    /**
     * Friendship States
     */
    const STATE_NONE = 0;
    const STATE_FRIENDS = 1;
    const STATE_REQUEST_RECEIVED = 2;
    const STATE_REQUEST_SENT = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_friendship';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'friend_user_id'], 'required'],
            [['user_id', 'friend_user_id'], 'integer'],
            [['user_id', 'friend_user_id'], 'unique', 'targetAttribute' => ['user_id', 'friend_user_id'], 'message' => 'The combination of User ID and Friend User ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'friend_user_id' => 'Friend User ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFriendUser()
    {
        return $this->hasOne(User::className(), ['id' => 'friend_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {

        if ($insert) {
            // Check if this is an request (friend has no entry in table)
            $state = self::getStateForUser($this->user, $this->friendUser);
            if ($state === self::STATE_REQUEST_SENT) {
                // Send Request Notification
                $notification = new \humhub\modules\friendship\notifications\Request();
                $notification->originator = $this->user;
                $notification->source = $this;
                $notification->send($this->friendUser);
            } elseif ($state === self::STATE_FRIENDS) {
                // Remove request notification
                $notification = new \humhub\modules\friendship\notifications\Request();
                $notification->originator = $this->friendUser;
                $notification->delete($this->user);

                // User approved friends request notification
                $notification = new \humhub\modules\friendship\notifications\RequestApproved();
                $notification->originator = $this->user;
                $notification->source = $this;
                $notification->send($this->friendUser);
            }
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Returns the friendship state between to users
     * 
     * @param User $user
     * @param User $friend
     * 
     * @return int the request state see self::STATE_*
     */
    public static function getStateForUser($user, $friend)
    {
        $state = self::STATE_NONE;

        if (self::getSentRequestsQuery($user)->andWhere(['user.id' => $friend->id])->one() !== null) {
            $state = self::STATE_REQUEST_SENT;
        } elseif (self::getFriendsQuery($user)->andWhere(['user.id' => $friend->id])->one() !== null) {
            $state = self::STATE_FRIENDS;
        } elseif (self::getReceivedRequestsQuery($user)->andWhere(['user.id' => $friend->id])->one() !== null) {
            $state = self::STATE_REQUEST_RECEIVED;
        }

        return $state;
    }

    /**
     * Returns a query for friends of a user
     * 
     * @return \yii\db\ActiveQuery
     * @param type $user
     */
    public static function getFriendsQuery($user)
    {
        $query = User::find();

        // Users which received a friend requests from given user
        $query->leftJoin('user_friendship recv', 'user.id=recv.friend_user_id AND recv.user_id=:userId', [':userId' => $user->id]);
        $query->andWhere(['IS NOT', 'recv.id', new \yii\db\Expression('NULL')]);

        // Users which send a friend request to given user
        $query->leftJoin('user_friendship snd', 'user.id=snd.user_id AND snd.friend_user_id=:userId', [':userId' => $user->id]);
        $query->andWhere(['IS NOT', 'snd.id', new \yii\db\Expression('NULL')]);

        return $query;
    }

    /**
     * Returns a query for sent and not approved friend requests of an user
     * 
     * @return \yii\db\ActiveQuery
     * @param type $user
     */
    public static function getSentRequestsQuery($user)
    {
        $query = User::find();

        // Users which received a friend requests from given user
        $query->leftJoin('user_friendship recv', 'user.id=recv.friend_user_id AND recv.user_id=:userId', [':userId' => $user->id]);
        $query->andWhere(['IS NOT', 'recv.id', new \yii\db\Expression('NULL')]);

        // Users which NOT send a friend request to given user
        $query->leftJoin('user_friendship snd', 'user.id=snd.user_id AND snd.friend_user_id=:userId', [':userId' => $user->id]);
        $query->andWhere(['IS', 'snd.id', new \yii\db\Expression('NULL')]);

        return $query;
    }

    /**
     * Returns a query for received and not responded friend requests of an user
     * 
     * @param User $user
     * @return \yii\db\ActiveQuery
     */
    public static function getReceivedRequestsQuery($user)
    {
        $query = User::find();

        // Users which NOT received a friend requests from given user
        $query->leftJoin('user_friendship recv', 'user.id=recv.friend_user_id AND recv.user_id=:userId', [':userId' => $user->id]);
        $query->andWhere(['IS', 'recv.id', new \yii\db\Expression('NULL')]);

        // Users which send a friend request to given user
        $query->leftJoin('user_friendship snd', 'user.id=snd.user_id AND snd.friend_user_id=:userId', [':userId' => $user->id]);
        $query->andWhere(['IS NOT', 'snd.id', new \yii\db\Expression('NULL')]);

        return $query;
    }

    /**
     * Adds a friendship or sends a request
     * 
     * @param User $user
     * @param User $friend
     */
    public static function add($user, $friend)
    {
        $friendship = new Friendship();
        $friendship->user_id = $user->id;
        $friendship->friend_user_id = $friend->id;
        $friendship->save();
    }

    /**
     * Cancels a friendship or request to a friend
     * 
     * @param User $user
     * @param User $friend
     */
    public static function cancel($user, $friend)
    {
        // Delete friends entry
        $myFriendship = Friendship::findOne(['user_id' => $user->id, 'friend_user_id' => $friend->id]);
        $friendsFriendship = Friendship::findOne(['user_id' => $friend->id, 'friend_user_id' => $user->id]);


        if ($friendsFriendship !== null) {
            $friendsFriendship->delete();
        }

        if ($myFriendship !== null) {
            $myFriendship->delete();
        } elseif ($friendsFriendship !== null) {
            // Is declined friendship request - send declined notification
            $notification = new \humhub\modules\friendship\notifications\RequestDeclined();
            $notification->originator = $user;
            $notification->send($friend);
        }
    }

}
