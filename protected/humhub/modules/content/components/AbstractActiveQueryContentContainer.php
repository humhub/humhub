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
    public const MAX_SEARCH_NEEDLES = 5;

    /**
     * During search, keyword will be walked through and each character of the set will be changed to another
     * of the same set to create variants to maximise search.
     * @var array
     */
    protected $multiCharacterSearchVariants = [['\'', '’', '`'], ['"', '”', '“']];

    /**
     * Filter query by visible records for the given or current user
     *
     * @param User|null $user
     * @return ActiveQuery
     */
    abstract public function visible(?User $user = null): ActiveQuery;

    /**
     * Returns a list of fields to be included in a container search.
     * If additional tables are needed, they must be added via `joinWith`.
     *
     * @return array
     */
    abstract protected function getSearchableFields(): array;

    /**
     * Returns a list of fields with its associative array of values and titles.
     * Only values of the fields are stored in DB, so we need to do a searching
     * in titles which may be translatable, e.g. counties list.
     * If additional tables are needed, they must be added via `joinWith`.
     *
     * @return array
     */
    abstract protected function getSearchableFieldTitles(): array;

    /**
     * Performs a container text search
     *
     * @param string $keywords
     * @param array|null $fields if empty the fields will be used from the method getSearchableFields()
     * @return self
     */
    public function search(string $keywords, ?array $fields = null): ActiveQuery
    {
        if (empty($keywords)) {
            return $this;
        }

        foreach ($this->setUpKeywords($keywords) as $keyword) {
            $this->searchKeyword($keyword, $fields);
        }

        return $this;
    }

    /**
     * @param string|array $keywords
     * @return array
     */
    private function setUpKeywords($keywords): array
    {
        if (!is_array($keywords)) {
            $keywords = explode(' ', $keywords);
        }

        return array_slice($keywords, 0, static::MAX_SEARCH_NEEDLES);
    }

    /**
     * @return self
     */
    private function searchKeyword(string $keyword, ?array $fields = null): ActiveQuery
    {
        if (empty($fields)) {
            $fields = $this->getSearchableFields();
        }

        $conditions = [];
        foreach ($this->prepareKeywordVariants($keyword) as $variant) {
            $subConditions = [];

            foreach ($fields as $field) {
                $subConditions[] = ['LIKE', $field, $variant];
            }

            $conditions[] = array_merge(['OR'], $subConditions);
        }

        $fieldTitles = $this->getSearchableFieldTitles();
        foreach ($fieldTitles as $field => $titles) {
            $valueKeys = [];
            foreach ($titles as $key => $title) {
                if (stripos($title, $keyword) === 0) {
                    $valueKeys[] = $key;
                }
            }
            if ($valueKeys !== []) {
                $conditions[] = ['IN', $field, $valueKeys];
            }
        }

        return $this->andWhere(array_merge(['OR'], $conditions));
    }

    /**
     * This function will look through keyword and prepare other variants of the words according to config
     * This is used to search for different apostrophes and quotes characters as for now.
     * Example: word "o'Surname", will create array ["o'Surname", "o’Surname", "o`Surname"]
     *
     * @param $keyword
     * @return array
     */
    private function prepareKeywordVariants($keyword): array
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
}
