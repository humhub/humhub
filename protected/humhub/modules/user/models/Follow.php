<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\modules\user\events\FollowEvent;
use humhub\modules\activity\models\Activity;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

/**
 * This is the model class for table "user_follow".
 *
 * @property integer $id
 * @property string $object_model
 * @property integer $object_id
 * @property integer $user_id
 * @property integer $send_notifications
 */
class Follow extends \yii\db\ActiveRecord
{

    /**
     * @event \humhub\modules\user\events\FollowEvent
     */
    const EVENT_FOLLOWING_CREATED = 'followCreated';

    /**
     * @event \humhub\modules\user\events\FollowEvent
     */
    const EVENT_FOLLOWING_REMOVED = 'followRemoved';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_follow';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Set send_notifications to 0 by default
        if ($this->send_notifications === null) {
            $this->send_notifications = 0;
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \humhub\components\behaviors\PolymorphicRelation::className(),
                'mustBeInstanceOf' => [
                    \yii\db\ActiveRecord::className(),
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_model', 'object_id', 'user_id'], 'required'],
            [['object_id', 'user_id'], 'integer'],
            [['send_notifications'], 'boolean'],
            [['object_model'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert && $this->object_model == User::className()) {
            \humhub\modules\user\notifications\Followed::instance()
                    ->from($this->user)
                    ->about($this)
                    ->send($this->getTarget());

            \humhub\modules\user\activities\UserFollow::instance()
                    ->from($this->user)
                    ->container($this->user)
                    ->about($this)
                    ->save();
        }

        $this->trigger(Follow::EVENT_FOLLOWING_CREATED, new FollowEvent(['user' => $this->user, 'target' => $this->getTarget()]));

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        $this->trigger(Follow::EVENT_FOLLOWING_REMOVED, new FollowEvent(['user' => $this->user, 'target' => $this->getTarget()]));

        // ToDo: Handle this via event of User Module
        if ($this->object_model == User::className()) {
            $notification = new \humhub\modules\user\notifications\Followed();
            $notification->originator = $this->user;
            $notification->delete($this->getTarget());

            foreach (Activity::findAll(['object_model' => $this->className(), 'object_id' => $this->id]) as $activity) {
                $activity->delete();
            }
        }

        return parent::beforeDelete();
    }

    public function getUser()
    {
        return $this->hasOne(\humhub\modules\user\models\User::className(), ['id' => 'user_id']);
    }

    public function getTarget()
    {
        $targetClass = $this->object_model;
        if ($targetClass != "") {
            return $targetClass::findOne(['id' => $this->object_id]);
        }
        return null;
    }

    /**
     * Returns all followed spaces of the given user as ActiveQuery.
     * If $withNotifications is set only follower with the given send_notifications setting are returned.
     * 
     * @param \humhub\modules\user\models\User $user
     * @param boolean|null $withNotifications by notification setting (default is null without notification handling)
     * @return \yii\db\ActiveQuery Space query of all followed spaces
     * @since 1.2
     */
    public static function getFollowedSpacesQuery(User $user, $withNotifications = null)
    {
        $subQuery = self::find()
                ->where(['user_follow.user_id' => $user->id, 'user_follow.object_model' => Space::class])
                ->andWhere('user_follow.object_id=space.id');
        
        if ($withNotifications === true) {
            $subQuery->andWhere(['user_follow.send_notifications' => 1]);
        } else if ($withNotifications === false) {
            $subQuery->andWhere(['user_follow.send_notifications' => 0]);
        }
        
        return Space::find()->where(['exists', $subQuery]);
    }

    /**
     * Returns all active users following the given $target record.
     * If $withNotifications is set only follower with the given send_notifications setting are returned.
     * 
     * @param \yii\db\ActiveRecord $target
     * @param type $withNotifications
     * @return type
     */
    public static function getFollowersQuery(\yii\db\ActiveRecord $target, $withNotifications = null)
    {
        $subQuery = self::find()
                ->where(['user_follow.object_model' => $target->className(), 'user_follow.object_id' => $target->getPrimaryKey()])
                ->andWhere('user_follow.user_id=user.id');
        
        if ($withNotifications === true) {
            $subQuery->andWhere(['user_follow.send_notifications' => 1]);
        } else if ($withNotifications === false) {
            $subQuery->andWhere(['user_follow.send_notifications' => 0]);
        }
        
        return User::find()->active()->andWhere(['exists', $subQuery]);
    }

}
