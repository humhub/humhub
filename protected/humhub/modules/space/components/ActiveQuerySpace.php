<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */


namespace humhub\modules\space\components;

use humhub\events\ActiveQueryEvent;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;
use yii\db\ActiveQuery;


/**
 * ActiveQuerySpace is used to query Space records.
 *
 * @since 1.4
 */
class ActiveQuerySpace extends ActiveQuery
{
    const MAX_SEARCH_NEEDLES = 5;

    /**
     * @event Event an event that is triggered when only visible spaces are requested via [[visible()]].
     */
    const EVENT_CHECK_VISIBILITY = 'checkVisibility';

    /**
     * Only returns spaces which are visible for this user
     *
     * @param User|null $user
     * @return ActiveQuerySpace the query
     */
    public function visible(User $user = null)
    {
        $this->trigger(self::EVENT_CHECK_VISIBILITY, new ActiveQueryEvent(['query' => $this]));

        if ($user === null && !Yii::$app->user->isGuest) {
            try {
                $user = Yii::$app->user->getIdentity();
            } catch (\Throwable $e) {
                Yii::error($e, 'space');
            }
        }

        if ($user !== null) {
            $this->andWhere(['OR',
                ['IN', 'space.visibility', [Space::VISIBILITY_ALL, Space::VISIBILITY_REGISTERED_ONLY]],
                ['AND',
                    ['=', 'space.visibility', Space::VISIBILITY_NONE],
                    ['IN', 'space.id', Membership::find()->select('space_id')->where(['user_id' => $user->id])]
                ]
            ]);
        } else {
            $this->andWhere(['!=', 'space.visibility', Space::VISIBILITY_NONE]);
        }

        return $this;
    }

    /**
     * Performs a space full text search
     *
     * @param string|array $keywords
     * @param array $columns
     * @return ActiveQuerySpace the query
     */
    public function search($keywords, $columns = ['space.name', 'space.description', 'contentcontainer.tags_cached'])
    {
        if (empty($keywords)) {
            return $this;
        }

        $this->joinWith('contentContainerRecord');

        if (!is_array($keywords)) {
            $keywords = explode(' ', $keywords);
        }

        foreach (array_slice($keywords, 0, static::MAX_SEARCH_NEEDLES) as $keyword) {
            $conditions = [];
            foreach ($columns as $field) {
                $conditions[] = ['LIKE', $field, $keyword];
            }
            $this->andWhere(array_merge(['OR'], $conditions));
        }

        return $this;
    }

    /**
     * Exclude blocked spaces for the given $user or for the current User
     *
     * @param User $user
     * @return ActiveQueryUser the query
     */
    public function filterBlockedSpaces(?User $user = null): ActiveQuerySpace
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

        $this->leftJoin('contentcontainer_blocked_users', 'contentcontainer_blocked_users.contentcontainer_id=space.contentcontainer_id AND contentcontainer_blocked_users.user_id=:blockedUserId', [':blockedUserId' => $user->id]);
        $this->andWhere('contentcontainer_blocked_users.user_id IS NULL');

        return $this;
    }
}
