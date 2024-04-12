<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\components;

use humhub\events\ActiveQueryEvent;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\content\components\AbstractActiveQueryContentContainer;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Throwable;
use Yii;
use yii\db\ActiveQuery;

/**
 * ActiveQuerySpace is used to query Space records.
 *
 * @since 1.4
 */
class ActiveQuerySpace extends AbstractActiveQueryContentContainer
{
    /**
     * @event Event an event that is triggered when only visible spaces are requested via [[visible()]].
     */
    public const EVENT_CHECK_VISIBILITY = 'checkVisibility';

    /**
     * Only returns spaces which are visible for this user
     *
     * @inheritdoc
     * @return self
     */
    public function visible(?User $user = null): ActiveQuery
    {
        $this->trigger(self::EVENT_CHECK_VISIBILITY, new ActiveQueryEvent(['query' => $this]));

        if ($user === null && !Yii::$app->user->isGuest) {
            try {
                $user = Yii::$app->user->getIdentity();
            } catch (Throwable $e) {
                Yii::error($e, 'space');
            }
        }

        if ($user !== null) {
            if ($user->can(ManageSpaces::class)) {
                return $this;
            }

            $this->andWhere(['OR',
                ['IN', 'space.visibility', [Space::VISIBILITY_ALL, Space::VISIBILITY_REGISTERED_ONLY]],
                ['AND',
                    ['=', 'space.visibility', Space::VISIBILITY_NONE],
                    ['IN', 'space.id', Membership::find()->select('space_id')->where(['user_id' => $user->id])],
                ],
            ]);
        } else {
            $this->andWhere(['!=', 'space.visibility', Space::VISIBILITY_NONE]);
        }

        return $this;
    }

    /**
     * @inerhitdoc
     */
    protected function getSearchableFields(): array
    {
        $this->joinWith('contentContainerRecord');

        return ['space.name', 'space.description', 'contentcontainer.tags_cached'];
    }

    /**
     * @inerhitdoc
     */
    protected function getSearchableFieldTitles(): array
    {
        return [];
    }

    /**
     * Exclude blocked spaces for the given $user or for the current User
     *
     * @param User|null $user
     * @return self
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

    /**
     * @return ActiveQuerySpace
     */
    public function defaultOrderBy(): ActiveQuerySpace
    {
        $this->orderBy(['space.sort_order' => SORT_ASC, 'space.name' => SORT_ASC]);
        return $this;
    }
}
