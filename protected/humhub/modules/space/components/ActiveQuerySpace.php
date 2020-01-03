<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */


namespace humhub\modules\space\components;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;


/**
 * ActiveQuerySpace is used to query Space records.
 *
 * @since 1.4
 */
class ActiveQuerySpace extends ActiveQuery
{
    const MAX_SEARCH_NEEDLES = 5;

    /**
     * Only returns spaces which are visible for this user
     *
     * @param User|null $user
     * @return ActiveQuerySpace the query
     */
    public function visible(User $user = null)
    {
        if ($user === null && !Yii::$app->user->isGuest) {
            try {
                $user = Yii::$app->user->getIdentity();
            } catch (\Throwable $e) {
                Yii::error($e, 'space');
            }
        }

        if ($user !== null) {

            $spaceIds = array_map(function (Membership $membership) {
                return $membership->space_id;
            }, Membership::findAll(['user_id' => $user->id]));

            $this->andWhere(['OR',
                ['!=', 'space.visibility', Space::VISIBILITY_NONE],
                ['IN', 'space.id', $spaceIds]
            ]);
        } else {
            $this->andWhere(['space.visibility' => Space::VISIBILITY_ALL]);
        }
        return $this;
    }

    /**
     * Performs a space full text search
     *
     * @param string|array $keywords
     *
     * @return ActiveQuerySpace the query
     */
    public function search($keywords)
    {
        if (empty($keywords)) {
            return $this;
        }

        if (!is_array($keywords)) {
            $keywords = explode(' ', $keywords);
        }

        foreach (array_slice($keywords, 0, static::MAX_SEARCH_NEEDLES) as $keyword) {
            $conditions = [];
            foreach (['space.name', 'space.description', 'space.tags'] as $field) {
                $conditions[] = ['LIKE', $field, $keyword];
            }
            $this->andWhere(array_merge(['OR'], $conditions));
        }

        return $this;
    }
}
