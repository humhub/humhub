<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\components\behaviors\PolymorphicRelation;
use humhub\modules\user\activities\UserFollow;
use humhub\modules\user\notifications\Followed;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\events\FollowEvent;
use humhub\modules\activity\models\Activity;
use humhub\modules\space\models\Space;

/**
 * This is the model class for table "user_follow".
 *
 * @property integer $id
 * @property string $object_model
 * @property integer $object_id
 * @property integer $user_id
 * @property integer $send_notifications
 */
class Follow extends ActiveRecord
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
                'class' => PolymorphicRelation::class,
                'mustBeInstanceOf' => [
                    ActiveRecord::class,
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
     * @throws InvalidConfigException
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert && $this->send_notifications && $this->object_model == User::class) {
            Followed::instance()
                    ->from($this->user)
                    ->about($this)
                    ->send($this->getTarget());

            UserFollow::instance()
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
        if($this->getTarget()) {
            $this->trigger(Follow::EVENT_FOLLOWING_REMOVED, new FollowEvent(['user' => $this->user, 'target' => $this->getTarget()]));

            // ToDo: Handle this via event of User Module
            if ($this->object_model === User::class) {
                $notification = new Followed();
                $notification->originator = $this->user;
                $notification->delete($this->getTarget());

                foreach (Activity::findAll(['object_model' => static::class, 'object_id' => $this->id]) as $activity) {
                    $activity->delete();
                }
            }
        }
        return parent::beforeDelete();
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getTarget()
    {
        try {
            $targetClass = $this->object_model;
            if ($targetClass != "" && is_subclass_of($targetClass, ActiveRecord::class)) {
                return $targetClass::findOne(['id' => $this->object_id]);
            }
        } catch(\Exception $e) {
            // Avoid errors in integrity check
            Yii::error($e);
        }
        return null;
    }

    /**
     * Returns all followed spaces of the given user as ActiveQuery.
     * If $withNotifications is set only follower with the given send_notifications setting are returned.
     *
     * @param User $user
     * @param boolean|null $withNotifications by notification setting (default is null without notification handling)
     * @return ActiveQuery Space query of all followed spaces
     * @since 1.2
     */
    public static function getFollowedSpacesQuery(User $user, $withNotifications = null)
    {
        $subQuery = self::find()
                ->where(['user_follow.user_id' => $user->id, 'user_follow.object_model' => Space::class])
                ->andWhere('user_follow.object_id=space.id');

        if ($withNotifications === true) {
            $subQuery->andWhere(['user_follow.send_notifications' => 1]);
        } elseif ($withNotifications === false) {
            $subQuery->andWhere(['user_follow.send_notifications' => 0]);
        }

        return Space::find()->where(['exists', $subQuery]);
    }

    /**
     * @param User $user
     * @param null $withNotifications
     * @return ActiveQueryUser
     */
    public static function getFollowedUserQuery(User $user, $withNotifications = null)
    {
        $subQuery = self::find()
            ->where(['user_follow.user_id' => $user->id, 'user_follow.object_model' => User::class])
            ->andWhere('user_follow.object_id=user.id');

        if ($withNotifications === true) {
            $subQuery->andWhere(['user_follow.send_notifications' => 1]);
        } elseif ($withNotifications === false) {
            $subQuery->andWhere(['user_follow.send_notifications' => 0]);
        }

        return User::find()->where(['exists', $subQuery]);
    }

    /**
     * Returns a query searching for all container ids the user is following. If $containerClass is given we only search
     * for a certain container type.
     *
     * @param User $user
     * @param string $containerClass
     * @return Query
     * @since 1.8
     */
    public static function getFollowedContainerIdQuery(User $user, $containerClass = null)
    {
        return (new Query())
            ->select("contentcontainer.id AS id")
            ->from('user_follow')
            ->innerJoin('contentcontainer', 'contentcontainer.pk = user_follow.object_id AND contentcontainer.class = user_follow.object_model')
            ->where(['user_follow.user_id' => $user->id])
            ->indexBy('id')
            ->andWhere($containerClass
                    ? ['user_follow.object_model' => $containerClass]
                    : ['OR', ['user_follow.object_model' => Space::class], ['user_follow.object_model' => User::class]]);
    }

    /**
     * Returns all active users following the given $target record.
     * If $withNotifications is set only follower with the given send_notifications setting are returned.
     *
     * @param ActiveRecord $target
     * @param boolean $withNotifications
     * @return ActiveQueryUser
     */
    public static function getFollowersQuery(ActiveRecord $target, $withNotifications = null)
    {
        $subQuery = self::find()
                ->where(['user_follow.object_model' => $target->className(), 'user_follow.object_id' => $target->getPrimaryKey()])
                ->andWhere('user_follow.user_id=user.id');

        if ($withNotifications === true) {
            $subQuery->andWhere(['user_follow.send_notifications' => 1]);
        } elseif ($withNotifications === false) {
            $subQuery->andWhere(['user_follow.send_notifications' => 0]);
        }

        return User::find()->visible()->andWhere(['exists', $subQuery]);
    }

}
