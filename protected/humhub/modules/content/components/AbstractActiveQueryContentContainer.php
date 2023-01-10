<?php

namespace humhub\modules\content\components;

use humhub\modules\user\models\User;
use yii\db\ActiveQuery;

/**
 * AbstractActiveQueryContentContainer is used for Active Query of the ContentContainerActiveRecord (Space, User)
 *
 * @since 1.13.1
 */
abstract class AbstractActiveQueryContentContainer extends ActiveQuery
{
    /**
     * Query keywords will be broken down into array needles with this length
     * Meaning, if you search for "word1 word2 word3" and MAX_SEARCH_NEEDLES being 2
     * word3 will be left out, and search will only look for word1, word2.
     *
     * @var int
     */
    const MAX_SEARCH_NEEDLES = 5;

    /**
     * Filter query by visible records for the given or current user
     *
     * @param User|null $user
     * @return ActiveQuery
     */
    abstract public function visible(?User $user = null): ActiveQuery;

    /**
     * Performs a container text search
     *
     * @param string|array $keywords
     * @param array|null $fields if empty the fields will be used from the method getSearchableFields()
     * @return ActiveQuery
     */
    abstract public function search($keywords, ?array $fields = null): ActiveQuery;

    /**
     * Returns a list of fields to be included in a container search.
     *
     * @return array
     */
    abstract protected function getSearchableFields(): array;

    /**
     * Filter this query by keyword
     *
     * @param string $keyword
     * @param array|null $fields if empty the fields will be used from the method getSearchableFields()
     * @return ActiveQuery
     */
    abstract public function searchKeyword(string $keyword, ?array $fields = null): ActiveQuery;

    /**
     * @param string|array $keywords
     * @return array
     */
    protected function setUpKeywords($keywords): array
    {
        if (!is_array($keywords)) {
            $keywords = explode(' ', $keywords);
        }

        return array_slice($keywords, 0, static::MAX_SEARCH_NEEDLES);
    }
}