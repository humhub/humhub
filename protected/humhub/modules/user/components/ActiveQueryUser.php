<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\events\ActiveQueryEvent;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\user\models\fieldtype\BaseTypeVirtual;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User as UserModel;
use humhub\modules\user\Module;
use Yii;
use yii\db\ActiveQuery;

/**
 * ActiveQueryUser is used to query User records.
 *
 * @author luke
 */
class ActiveQueryUser extends ActiveQuery
{
    /**
     * Query keywords will be broken down into array needles with this length
     * Meaning, if you search for "word1 word2 word3" and MAX_SEARCH_NEEDLES being 2
     * word3 will be left out, and search will only look for word1, word2.
     *
     * @var string
     */
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
     * For toggling on condition if required
     * @var bool
     */
    protected $multiCharacterSearch = true;

    /**
     * During search, keyword will be walked through and each character of the set will be changed to another
     * of the same set to create variants to maximise search.
     * @var array
     */
    protected $multiCharacterSearchVariants = [['\'', '’', '`'], ['"', '”', '“']];

    /**
     * Limit to active users
     *
     * @return ActiveQueryUser the query
     */
    public function active()
    {
        $this->trigger(self::EVENT_CHECK_ACTIVE, new ActiveQueryEvent(['query' => $this]));
        return $this->andWhere(['user.status' => UserModel::STATUS_ENABLED]);
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

        $allowedVisibilities = [UserModel::VISIBILITY_ALL];
        if (!Yii::$app->user->isGuest) {
            $allowedVisibilities[] = UserModel::VISIBILITY_REGISTERED_ONLY;
        }

        return $this->active()
            ->andWhere(['IN', 'user.visibility', $allowedVisibilities]);
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

        $this->joinWith('profile')->joinWith('contentContainerRecord');

        foreach ($this->setUpKeywords($keywords) as $keyword) {
            $this->andWhere(array_merge(['OR'], $this->createKeywordCondition($keyword, $fields)));
        }

        return $this;
    }

    /**
     * @param $keyword
     * @param null $fields
     * @return array
     */
    protected function createKeywordCondition($keyword, $fields = null)
    {
        if (empty($fields)) {
            $fields = $this->getSearchableUserFields();
        }

        $conditions = [];
        foreach ($this->prepareKeywordVariants($keyword) as $variant) {
            $subConditions = [];

            foreach ($fields as $field) {
                $subConditions[] = ['LIKE', $field, $variant];
            }

            $conditions[] = array_merge(['OR'], $subConditions);
        }

        return $conditions;
    }

    /**
     * @param $keywords
     * @return array
     */
    protected function setUpKeywords($keywords)
    {
        if (!is_array($keywords)) {
            $keywords = explode(' ', $keywords);
        }

        return array_slice($keywords, 0, static::MAX_SEARCH_NEEDLES);
    }

    /**
     * This function will look through keyword and prepare other variants of the words according to config
     * This is used to search for different apostrophes and quotes characters as for now.
     * Example: word "o'Surname", will create array ["o'Surname", "o’Surname", "o`Surname"]
     *
     * @param $keyword
     * @return array
     */
    protected function prepareKeywordVariants($keyword)
    {
        $variants = [$keyword];

        foreach ($this->multiCharacterSearchVariants as $set) {
            foreach ($set as $character) {
                if (strpos($keyword, $character) === false) {
                    continue;
                }

                foreach ($set as $replaceWithCharacter) {
                    if ($character === $replaceWithCharacter) {
                        continue;
                    }

                    $variants[] = str_replace($character, $replaceWithCharacter, $keyword);
                }
            }
        }

        return $variants;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setMultiCharacterSearch($value)
    {
        $this->multiCharacterSearch = (bool)$value;
        return $this;
    }

    /**
     * @param $array
     * @return $this
     */
    public function setMultiCharacterSearchVariants($array)
    {
        $this->multiCharacterSearchVariants = $array;
        return $this;
    }

    /**
     * Returns a list of fields to be included in a user search.
     *
     * @return array
     */
    private function getSearchableUserFields()
    {
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

    /**
     * Exclude blocked users for the given $user or for the current User
     *
     * @param UserModel $user
     * @return ActiveQueryUser the query
     */
    public function filterBlockedUsers(?UserModel $user = null): ActiveQueryUser
    {
        if ($user === null && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        }

        if (!($user instanceof UserModel)) {
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

}
