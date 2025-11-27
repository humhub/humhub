<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\events\ActiveQueryEvent;
use humhub\modules\admin\models\GroupSearch;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\content\components\AbstractActiveQueryContentContainer;
use humhub\modules\user\models\fieldtype\BaseTypeVirtual;
use humhub\modules\user\models\fieldtype\CountrySelect;
use humhub\modules\user\models\fieldtype\Select;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;

/**
 * ActiveQueryUser is used to query User records.
 *
 * @author luke
 */
class ActiveQueryUser extends AbstractActiveQueryContentContainer
{
    /**
     * @event Event an event that is triggered when only visible users are requested via [[visible()]].
     */
    public const EVENT_CHECK_VISIBILITY = 'checkVisibility';

    /**
     * @event Event an event that is triggered when only active users are requested via [[active()]].
     */
    public const EVENT_CHECK_ACTIVE = 'checkActive';

    /**
     * Limit to active users
     *
     * @return ActiveQueryUser the query
     */
    public function active()
    {
        $this->trigger(self::EVENT_CHECK_ACTIVE, new ActiveQueryEvent(['query' => $this]));
        return $this->andWhere(['user.status' => User::STATUS_ENABLED]);
    }

    /**
     * Returns only users that should appear in user lists or in the search results.
     * Also only active (enabled) users are returned.
     *
     * @return self
     * @since 1.2.3
     * @inheritdoc
     */
    public function visible(?User $user = null): ActiveQuery
    {
        $this->trigger(self::EVENT_CHECK_VISIBILITY, new ActiveQueryEvent(['query' => $this]));

        $this->active();

        if ($user === null && !Yii::$app->user->isGuest) {
            try {
                $user = Yii::$app->user->getIdentity();
            } catch (Throwable $e) {
                Yii::error($e, 'user');
            }
        }

        $allowedVisibilities = [User::VISIBILITY_ALL];
        if ($user === null) {
            // Guest can view only public users
            return $this->andWhere(['IN', 'user.visibility', $allowedVisibilities]);
        }

        if ((new PermissionManager(['subject' => $user]))->can(ManageUsers::class)) {
            // Admin/manager can view users with any visibility status
            return $this;
        }

        $allowedVisibilities[] = User::VISIBILITY_REGISTERED_ONLY;

        return $this->andWhere(['OR',
            ['user.id' => $user->id], // User also can view own profile
            ['IN', 'user.visibility', $allowedVisibilities],
        ]);
    }


    /**
     * Adds default user order (e.g. by lastname)
     *
     * @return ActiveQueryUser the query
     */
    public function defaultOrder()
    {
        $this->joinWith('profile');
        $this->addOrderBy(['profile.lastname' => SORT_ASC]);

        return $this;
    }


    /**
     * @inheritdoc
     */
    protected function getSearchableFields(): array
    {
        $this->joinWith('profile')->joinWith('contentContainerRecord');

        $fields = ['user.username', 'contentcontainer.tags_cached'];

        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        if ($module->includeEmailInSearch) {
            $fields[] = 'user.email';
        }

        foreach (ProfileField::findAll(['searchable' => 1]) as $profileField) {
            if (!($profileField->getFieldType() instanceof BaseTypeVirtual)) {
                $fields[] = 'profile.' . $profileField->internal_name;
            }
        }

        return $fields;
    }

    /**
     * @inerhitdoc
     */
    protected function getSearchableFieldTitles(): array
    {
        $this->joinWith('profile')->joinWith('contentContainerRecord');

        $fields = [];

        $profileFields = ProfileField::find()
            ->where(['searchable' => 1])
            ->andWhere(['IN', 'field_type_class', [CountrySelect::class, Select::class]]);

        foreach ($profileFields->all() as $profileField) {
            /* @var ProfileField $profileField */
            $fieldType = $profileField->getFieldType();
            if ($fieldType instanceof Select) {
                $fields['profile.' . $profileField->internal_name] = $fieldType->getSelectItems();
            }
        }

        return $fields;
    }

    /**
     * Limits the query to a specified user group
     *
     * @param Group $group
     * @param bool $includeSubGroups
     * @return ActiveQueryUser the query
     */
    public function isGroupMember(Group $group, bool $includeSubGroups = false): self
    {
        $groupIds = $includeSubGroups
            ? Group::find()
                ->select('id')
                ->where(['parent_group_id' => $group->id])
                ->orWhere(['id' => $group->id])
            : $group->id;

        return $this->leftJoin(GroupUser::tableName(), User::tableName() . '.id = ' . GroupUser::tableName() . '.user_id')
            ->andWhere([GroupUser::tableName() . '.group_id' => $groupIds]);
    }

    /**
     * Returns only users which are administrable by the given user.
     *
     * @param User $user
     * @return ActiveQueryUser the query
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function administrableBy(User $user)
    {
        if (!(new PermissionManager(['subject' => $user]))->can([ManageUsers::class])) {
            $this->innerJoin(GroupUser::tableName(), User::tableName() . '.id = ' . GroupUser::tableName() . '.user_id')
                ->innerJoin(Group::tableName(), Group::tableName() . '.id = ' . GroupUser::tableName() . '.group_id')
                ->andWhere(GroupSearch::getGroupManagerQueryCondition($user));
        }

        return $this;
    }

    /**
     * Exclude blocked users for the given $user or for the current User
     *
     * @param User $user
     * @return ActiveQueryUser the query
     */
    public function filterBlockedUsers(?User $user = null): ActiveQueryUser
    {
        if ($user === null && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        }

        if (!($user instanceof User)) {
            return $this;
        }

        /* @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        if (!$userModule->allowBlockUsers()) {
            return $this;
        }

        $this->leftJoin('contentcontainer_blocked_users', 'contentcontainer_blocked_users.contentcontainer_id=user.contentcontainer_id AND contentcontainer_blocked_users.user_id=:blockedUserId', [':blockedUserId' => $user->id]);
        $this->andWhere('contentcontainer_blocked_users.user_id IS NULL');

        return $this;
    }

    /**
     * Filter users which are available for the given $user or for the current User
     *
     * @param User|null $user
     * @return ActiveQueryUser
     * @since 1.13
     */
    public function available(?User $user = null): ActiveQueryUser
    {
        return $this->visible($user)->filterBlockedUsers($user);
    }

}
