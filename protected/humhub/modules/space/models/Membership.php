<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\models;

use humhub\components\ActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\live\Module;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use InvalidArgumentException;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;

/**
 * This is the model class for table "space_membership".
 *
 * @property int $id
 * @property int $space_id
 * @property int $user_id
 * @property string|null $originator_user_id
 * @property int|null $status
 * @property string|null $request_message
 * @property string|null $last_visit
 * @property int $show_at_dashboard
 * @property int $can_cancel_membership
 * @property string $group_id
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 * @property int $send_notifications
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
    public const EVENT_MEMBER_REMOVED = 'memberRemoved';

    /**
     * @event \humhub\modules\space\MemberEvent
     */
    public const EVENT_MEMBER_ADDED = 'memberAdded';

    /**
     * Status Codes
     */
    public const STATUS_INVITED = 1;
    public const STATUS_APPLICANT = 2;
    public const STATUS_MEMBER = 3;

    public const USER_SPACES_CACHE_KEY = 'userSpaces_';
    public const USER_SPACEIDS_CACHE_KEY = 'userSpaceIds_';


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
            [['last_visit', 'created_at', 'group_id', 'updated_at'], 'safe'],
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
            'can_leave' => 'Can Leave',
        ];
    }

    /**
     * Determines if this membership is a full accepted membership.
     *
     * @return bool
     * @since v1.2.1
     */
    public function isMember()
    {
        return $this->status == self::STATUS_MEMBER;
    }

    /**
     * @return bool
     * @since 1.13
     */
    public function isPrivileged(): bool
    {
        return ($this->isMember()
            && in_array($this->group_id, [
                Space::USERGROUP_OWNER,
                Space::USERGROUP_ADMIN,
                Space::USERGROUP_MODERATOR,
            ]));
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

    public function afterSave($insert, $changedAttributes)
    {
        static::unsetCache($this->space_id, $this->user_id);

        parent::afterSave($insert, $changedAttributes);
    }

    public function beforeDelete()
    {
        Yii::$app->cache->delete(self::USER_SPACES_CACHE_KEY . $this->user_id);
        Yii::$app->cache->delete(self::USER_SPACEIDS_CACHE_KEY . $this->user_id);
        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        static::unsetCache($this->space_id, $this->user_id);

        parent::afterDelete();
    }

    /**
     * Update last visit
     */
    public function updateLastVisit()
    {
        $this->last_visit = date('Y-m-d G:i:s');
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
     * @param bool $cached use cached result if available
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
     * @param int $userId
     * @return array|mixed
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
     * @param User $user
     * @param bool $memberOnly include only member status - no pending/invite states
     * @param bool|null $withNotifications include only memberships with sendNotification setting
     * @return ActiveQuery for space model
     * @since 1.0
     */
    public static function getUserSpaceQuery(User $user, $memberOnly = true, $withNotifications = null)
    {
        $query = Space::find();
        $query->visible();
        $query->leftJoin(
            'space_membership',
            'space_membership.space_id=space.id and space_membership.user_id=:userId',
            [':userId' => $user->id],
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
            $query->defaultOrderBy();
        } else {
            $query->orderBy(['space_membership.last_visit' => SORT_DESC]);
        }

        return $query;
    }

    /**
     * Returns an ActiveQuery selecting all memberships for the given $user.
     *
     * @param User $user
     * @param int $membershipStatus the status of the Space by default self::STATUS_MEMBER.
     * @param int $spaceStatus the status of the Space by default Space::STATUS_ENABLED.
     * @return ActiveQuery
     * @since 1.2
     */
    public static function findByUser(
        User $user = null,
        $membershipStatus = self::STATUS_MEMBER,
        $spaceStatus = Space::STATUS_ENABLED,
    ) {
        if (!$user) {
            $user = Yii::$app->user->getIdentity();
        }

        $query = Membership::find();

        if (Yii::$app->getModule('space')->settings->get('spaceOrder') == 0) {
            $query->orderBy(['space.sort_order' => SORT_ASC, 'space.name' => SORT_ASC]);
        } else {
            $query->orderBy(['space_membership.last_visit' => SORT_DESC]);
        }

        $query->joinWith('space')->where(['space_membership.user_id' => $user->id]);
        $query->joinWith('space.contentContainerRecord');

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
     * @param Space $space
     * @param bool $membersOnly Only return approved members
     * @param bool|null $withNotifications include only memberships with sendNotification setting
     * @return ActiveQueryUser
     * @since 1.1
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
     * Selects the container id of spaces the given users is a member of.
     *
     * @param User $user
     * @return Query
     * @since 1.8
     */
    public static function getMemberSpaceContainerIdQuery(User $user)
    {
        return (new Query())
            ->select("space.contentcontainer_id AS id")
            ->from('space')
            ->innerJoin('space_membership sm', 'space.id = sm.space_id')
            ->where('sm.user_id = :userId', [':userId' => $user->id])
            ->indexBy('id')
            ->andWhere('space.status = :spaceStatusEnabled', [':spaceStatusEnabled' => Space::STATUS_ENABLED]);
    }

    /**
     * Checks if the current logged in user is the related user of this membership record.
     *
     * @return bool
     * @since 1.3.9
     */
    public function isCurrentUser(): bool
    {
        return !Yii::$app->user->isGuest && Yii::$app->user->identity->id === $this->user_id;
    }

    /**
     * Find and cache Membership by space and user
     *
     * @param Space|int $space
     * @param User|int $user
     *
     * @return self|null
     */
    public static function findMembership($space, $user): ?self
    {
        if ($space instanceof Space) {
            $spaceId = $space->id;
        } elseif ($space === null || null === $spaceId = filter_var($space, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE)) {
            throw new InvalidArgumentException("Argument #2 (\$space) must be a Space object or space ID.");
        }

        if ($user instanceof User) {
            $userId = $user->id;
        } elseif ($user !== 0 && empty($user)) {
            $userId = Yii::$app->user->id;
        } elseif (null === $userId = filter_var($user, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE)) {
            throw new InvalidArgumentException("Argument #1 (\$user) must be a User object or user ID.");
        }

        return Yii::$app->runtimeCache->getOrSet(__CLASS__ . "_$spaceId-$userId", fn() => Membership::findOne(['user_id' => $userId, 'space_id' => $spaceId]));
    }

    public static function unsetCache(int $spaceId, int $userId)
    {
        Yii::$app->runtimeCache->delete(__CLASS__ . "_$spaceId-$userId");
        Yii::$app->cache->delete(Module::$legitimateCachePrefix . $userId);
    }
}
