<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */


namespace humhub\modules\space\components;

use humhub\modules\user\components\ActiveQueryUser;
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
     * Only returns spaces which are visible for this user
     *
     * @return ActiveQuerySpace the query
     */
    public function visible()
    {
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
