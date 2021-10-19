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
    protected $cleanUpSymbols = true;

    /**
     * During search, these characters will be changed or removed.
     * if key is numeric: value is character that will be replaced to empty string (removed)
     * If key is string: key is character that will be replaced with value
     * @var array
     */
    protected $cleanUpSymbolsArray = ['\'' => '_', '’' => '_'];

    /**
     * If during character clean up, any symbols where changed to "_", do not escape it, because in mysql like _ means
     * any character, this will allow use to find names with ' even if ’ is entered. This array is used apposed to
     * original LikeConditionBuilder.
     * @var array
     */
    protected $escapingReplacements = ['%' => '\%', '\\' => '\\\\'];

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
        $this->joinWith('contentContainerRecord');

        foreach ($this->setUpKeywords($keywords) as $keyword) {
            $conditions = [];

            foreach ($fields as $field) {
                $conditions[] = ['LIKE', $field, $keyword];
            }

            $clean = $this->cleanUpSymbols($keyword);
            if ($clean && $clean !== $keyword) { // if the word was clean up, add it to OR LIKE to maximize results
                foreach ($fields as $field) {
                    $conditions[] = ['LIKE', $field, $clean, $this->escapingReplacements];
                }
            }

            $this->andWhere(array_merge(['OR'], $conditions));
        }

        return $this;
    }

    /**
     * @param $keywords
     * @return array
     */
    protected function setUpKeywords($keywords){

        if (!is_array($keywords)) {
            $keywords = explode(' ', $keywords);
        }

        return array_slice($keywords, 0, static::MAX_SEARCH_NEEDLES);
    }

    /**
     * @param $value
     */
    public function setCleanUpSymbols($value)
    {
        $this->cleanUpSymbols = (bool)$value;
    }

    /**
     * @param $text
     * @return string
     */
    protected function cleanUpSymbols($text)
    {
        if (!$this->cleanUpSymbols) {
            return false;
        }

        foreach ($this->cleanUpSymbolsArray as $key => $cleanUpSymbol) {
            $replaceWith = '';

            if (!is_numeric($key)) {
                $replaceWith = $cleanUpSymbol;
                $cleanUpSymbol = $key;
            }

            $text = str_replace($cleanUpSymbol, $replaceWith, $text);
        }

        return $text;
    }

    /**
     * Returns a list of fields to be included in a user search.
     *
     * @return array
     */
    private function getSearchableUserFields()
    {
        $fields = ['user.username', 'user.email', 'contentcontainer.tags_cached'];

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
