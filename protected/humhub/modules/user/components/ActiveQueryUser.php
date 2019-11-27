<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\ProfileField;
use yii\db\ActiveQuery;
use humhub\modules\user\models\User as UserModel;
use humhub\events\ActiveQueryEvent;

/**
 * ActiveQueryUser is used to query User records.
 *
 * @author luke
 */
class ActiveQueryUser extends ActiveQuery
{
    const MAX_SEARCH_NEEDLES = 5;

    /**
     * @event Event an event that is triggered when only visible users are requested via [[visible()]].
     */
    const EVENT_CHECK_VISIBILITY = 'checkVisibility';

    /**
     * @event Event an event that is triggered when only active users are requested via [[active()]].
     */
    const EVENT_CHECK_ACTIVE = 'checkActive';

    /**
     * Limit to active users
     *
     * @return ActiveQueryUser the query
     */
    public function active()
    {
        $this->trigger(self::EVENT_CHECK_ACTIVE, new ActiveQueryEvent(['query' => $this]));

        $this->andWhere(['user.status' => UserModel::STATUS_ENABLED]);
        return $this;
    }

    /**
     * Returns only users that should appear in user lists or in the search results.
     * Also only active (enabled) users are returned.
     *
     * @return ActiveQueryUser the query
     * @since 1.2.3
     */
    public function visible()
    {
        $this->trigger(self::EVENT_CHECK_VISIBILITY, new ActiveQueryEvent(['query' => $this]));
        return $this->active();
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
     * Performs a user full text search
     *
     * @param string|array $keywords
     * @param array|null $fields if empty all searchable profile fields will be used
     *
     * @return ActiveQueryUser the query
     */
    public function search($keywords, $fields = null)
    {
        if (empty($keywords)) {
            return $this;
        }

        if (empty($fields)) {
            $fields = $this->getSearchableUserFields();
        }

        $this->joinWith('profile');

        if (!is_array($keywords)) {
            $keywords = explode(' ', $keywords);
        }

        foreach (array_slice($keywords, 0, static::MAX_SEARCH_NEEDLES) as $keyword) {
            $conditions = [];
            foreach ($fields as $field) {
                $conditions[] = ['LIKE', $field, $keyword];
            }
            $this->andWhere(array_merge(['OR'], $conditions));
        }

        return $this;
    }

    /**
     * Returns a list of fields to be included in a user search.
     *
     * @return array
     */
    private function getSearchableUserFields()
    {
        $fields = ['user.username', 'user.email', 'user.tags'];
        foreach (ProfileField::findAll(['searchable' => 1]) as $profileField) {
            $fields[] = 'profile.' . $profileField->internal_name;
        }

        return $fields;
    }

    /**
     * Limits the query to a specified user group
     *
     * @param Group $group
     * @return ActiveQueryUser the query
     */
    public function isGroupMember(Group $group)
    {
        $this->leftJoin('group_user', 'user.id=group_user.user_id');
        $this->andWhere(['group_user.group_id' => $group->id]);

        return $this;
    }

    /**
     * Returns only users which are administrable by the given user.
     *
     * @param UserModel $user
     * @return ActiveQueryUser the query
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function administrableBy(UserModel $user)
    {

        if (!(new PermissionManager(['subject' => $user]))->can([ManageUsers::class])) {
            $this->joinWith('groups');

            $groupIds = [];
            foreach (GroupUser::find()->where(['user_id' => $user->id, 'is_group_manager' => 1])->all() as $gu) {
                $groupIds[] = $gu->group_id;
            }

            $this->andWhere(['IN', 'group.id', $groupIds]);
        }

        return $this;
    }

}
