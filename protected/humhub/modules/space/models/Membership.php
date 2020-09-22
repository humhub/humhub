<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\models;

use humhub\components\ActiveRecord;
use humhub\modules\user\models\User;
use humhub\modules\content\models\Content;
use Yii;

/**
 * This is the model class for table "space_membership".
 *
 * @property integer $id
 * @property integer $space_id
 * @property integer $user_id
 * @property string|null $originator_user_id
 * @property integer|null $status
 * @property string|null $request_message
 * @property string|null $last_visit
 * @property integer $show_at_dashboard
 * @property integer $can_cancel_membership
 * @property string $group_id
 * @property string|null $created_at
 * @property integer|null $created_by
 * @property string|null $updated_at
 * @property integer|null $updated_by
 * @property integer $send_notifications
 *
 * @property Space $space
 * @property User $user
 * @property User|null $originator
 */
class Membership extends ActiveRecord
{

    /**
     * @event \humhub\modules\space\MemberEvent
     */
    const EVENT_MEMBER_REMOVED = 'memberRemoved';

    /**
     * @event \humhub\modules\space\MemberEvent
     */
    const EVENT_MEMBER_ADDED = 'memberAdded';

    /**
     * Status Codes
     */
    const STATUS_INVITED = 1;
    const STATUS_APPLICANT = 2;
    const STATUS_MEMBER = 3;

    const USER_SPACES_CACHE_KEY = 'userSpaces_';
    const USER_SPACEIDS_CACHE_KEY = 'userSpaceIds_';


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'space_membership';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['space_id', 'user_id'], 'required'],
            [['space_id', 'user_id', 'originator_user_id', 'status', 'created_by', 'updated_by'], 'integer'],
            [['request_message'], 'string'],
            [['last_visit', 'created_at', 'group_id', 'updated_at'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'space_id' => 'Space ID',
            'user_id' => 'User ID',
            'originator_user_id' => Yii::t('SpaceModule.base', 'Originator User ID'),
            'status' => Yii::t('SpaceModule.base', 'Status'),
            'request_message' => Yii::t('SpaceModule.base', 'Request Message'),
            'last_visit' => Yii::t('SpaceModule.base', 'Last Visit'),
            'created_at' => Yii::t('SpaceModule.base', 'Created At'),
            'created_by' => Yii::t('SpaceModule.base', 'Created By'),
            'updated_at' => Yii::t('SpaceModule.base', 'Updated At'),
            'updated_by' => Yii::t('SpaceModule.base', 'Updated By'),
            'can_leave' => 'Can Leave'
        ];
    }

    /**
     * Determines if this membership is a full accepted membership.
     *
     * @since v1.2.1
     * @return bool
     */
    public function isMember()
    {
        return $this->status == self::STATUS_MEMBER;
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getOriginator()
    {
        return $this->hasOne(User::class, ['id' => 'originator_user_id']);
    }

    public function getSpace()
    {
        return $this->hasOne(Space::class, ['id' => 'space_id']);
    }

    public function beforeSave($insert)
    {
        Yii::$app->cache->delete(self::USER_SPACES_CACHE_KEY . $this->user_id);
        Yii::$app->cache->delete(self::USER_SPACEIDS_CACHE_KEY . $this->user_id);
        return parent::beforeSave($insert);
    }

    public function beforeDelete()
    {
        Yii::$app->cache->delete(self::USER_SPACES_CACHE_KEY . $this->user_id);
        Yii::$app->cache->delete(self::USER_SPACEIDS_CACHE_KEY . $this->user_id);
        return parent::beforeDelete();
    }

    /**
     * Update last visit
     */
    public function updateLastVisit()
    {
        $this->last_visit = new \yii\db\Expression('NOW()');
        $this->update(false, ['last_visit']);
    }

    /**
     * Counts all new Items for this membership
     */
    public function countNewItems()
    {
        $query = Content::find();
        $query->where(['stream_channel' => 'default']);
        $query->andWhere(['contentcontainer_id' => $this->space->contentContainerRecord->id]);
        $query->andWhere(['>', 'created_at', $this->last_visit]);

        return $query->count();
    }

    /**
     * Returns a list of all spaces of the given userId
     *
     * @param int|string $userId the user id or empty for current user
     * @param boolean $cached use cached result if available
     * @return Space[] an array of spaces
     */
    public static function getUserSpaces($userId = '', $cached = true)
    {
        if ($userId === '') {
            $userId = Yii::$app->user->id;
        }

        $cacheId = self::USER_SPACES_CACHE_KEY . $userId;

        $spaces = Yii::$app->cache->get($cacheId);
        if ($spaces === false || !$cached) {
            $spaces = [];
            foreach (static::getMembershipQuery($userId)->all() as $membership) {
                $spaces[] = $membership->space;
            }
            Yii::$app->cache->set($cacheId, $spaces);
        }

        return $spaces;
    }

    /**
     * Returns a list of all spaces' ids of the given userId
     *
     * @param integer $userId
     * @since 1.2.5
     */
    public static function getUserSpaceIds($userId = '')
    {
        if ($userId === '') {
            $userId = Yii::$app->user->id;
        }

        $cacheId = self::USER_SPACEIDS_CACHE_KEY . $userId;

        $spaceIds = Yii::$app->cache->get($cacheId);
        if ($spaceIds === false) {
            $spaceIds = static::getMembershipQuery($userId)->select('space_id')->column();
            Yii::$app->cache->set($cacheId, $spaceIds);
        }

        return $spaceIds;
    }

    private static function getMembershipQuery($userId)
    {
        $orderSetting = Yii::$app->getModule('space')->settings->get('spaceOrder');
        $orderBy = 'name ASC';
        if ($orderSetting != 0) {
            $orderBy = 'last_visit DESC';
        }

        $query = self::find()->joinWith('space')->orderBy($orderBy);
        $query->where(['user_id' => $userId, 'space_membership.status' => self::STATUS_MEMBER]);

        return $query;
    }

    /**
     * Returns Space for user space membership
     *
     * @since 1.0
     * @param \humhub\modules\user\models\User $user
     * @param boolean $memberOnly include only member status - no pending/invite states
     * @param boolean|null $withNotifications include only memberships with sendNotification setting
     * @return \yii\db\ActiveQuery for space model
     */
    public static function getUserSpaceQuery(User $user, $memberOnly = true, $withNotifications = null)
    {
        $query = Space::find();
        $query->leftJoin(
            'space_membership',
            'space_membership.space_id=space.id and space_membership.user_id=:userId',
            [':userId' => $user->id]
        );

        if ($memberOnly) {
            $query->andWhere(['space_membership.status' => self::STATUS_MEMBER]);
        }

        if ($withNotifications === true) {
            $query->andWhere(['space_membership.send_notifications' => 1]);
        } elseif ($withNotifications === false) {
            $query->andWhere(['space_membership.send_notifications' => 0]);
        }

        if (Yii::$app->getModule('space')->settings->get('spaceOrder') == 0) {
            $query->orderBy('name ASC');
        } else {
            $query->orderBy('last_visit DESC');
        }

        $query->orderBy(['name' => SORT_ASC]);

        return $query;
    }

    /**
     * Returns an ActiveQuery selcting all memberships for the given $user.
     *
     * @param User $user
     * @param integer $membershipStatus the status of the Space by default self::STATUS_MEMBER.
     * @param integer $spaceStatus the status of the Space by default Space::STATUS_ENABLED.
     * @return \yii\db\ActiveQuery
     * @since 1.2
     */
    public static function findByUser(
        User $user = null,
        $membershipStatus = self::STATUS_MEMBER,
        $spaceStatus = Space::STATUS_ENABLED
    ) {
        if (!$user) {
            $user = Yii::$app->user->getIdentity();
        }

        $query = Membership::find();

        if (Yii::$app->getModule('space')->settings->get('spaceOrder') == 0) {
            $query->orderBy('space.name ASC');
        } else {
            $query->orderBy('space_membership.last_visit DESC');
        }

        $query->joinWith('space')->where(['space_membership.user_id' => $user->id]);

        if ($spaceStatus) {
            $query->andWhere(['space.status' => $spaceStatus]);
        }

        if ($membershipStatus) {
            $query->andWhere(['space_membership.status' => $membershipStatus]);
        }

        return $query;
    }

    /**
     * Returns a user query for space memberships
     *
     * @since 1.1
     * @param Space $space
     * @param boolean $membersOnly Only return approved members
     * @param boolean|null $withNotifications include only memberships with sendNotification setting
     * @return \humhub\modules\user\components\ActiveQueryUser
     */
    public static function getSpaceMembersQuery(Space $space, $membersOnly = true, $withNotifications = null)
    {
        $query = User::find()->active();
        $query->join('LEFT JOIN', 'space_membership', 'space_membership.user_id=user.id');

        if ($membersOnly) {
            $query->andWhere(['space_membership.status' => self::STATUS_MEMBER]);
        }

        if ($withNotifications === true) {
            $query->andWhere(['space_membership.send_notifications' => 1]);
        } elseif ($withNotifications === false) {
            $query->andWhere(['space_membership.send_notifications' => 0]);
        }

        $query->andWhere(['space_id' => $space->id])->defaultOrder();

        return $query;
    }

    /**
     * Checks if the current logged in user is the related user of this membership record.
     *
     * @since 1.3.9
     * @return bool
     */
    public function isCurrentUser()
    {
        return !Yii::$app->user->isGuest && Yii::$app->user->identity->id === $this->user_id;
    }

}
