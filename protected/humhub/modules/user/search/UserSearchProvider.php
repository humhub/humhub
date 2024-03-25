<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\search;

use humhub\components\SearchProvider;
use humhub\modules\user\components\PeopleQuery;
use Yii;

/**
 * UserSearchProvider
 *
 * @author luke
 * @since 1.16
 */
class UserSearchProvider extends SearchProvider
{
    public bool $showOnEmpty = false;
    protected ?string $route = '/people';

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return Yii::t('UserModule.base', 'People');
    }

    /**
     * @inheritdoc
     */
    public function getAllResultsText(): string
    {
        return $this->hasRecords()
            ? Yii::t('base', 'Show all results')
            : Yii::t('UserModule.base', 'Advanced Profile Search');
    }

    /**
     * @inheritdoc
     */
    public function runSearch(): array
    {
        $peopleQuery = new PeopleQuery([
            'defaultFilters' => ['keyword' => $this->keyword],
            'pageSize' => $this->pageSize
        ]);

        $results = [];
        foreach ($peopleQuery->all() as $user) {
            $results[] = new SearchRecord($user);
        }

        return [
            'totalCount' => $peopleQuery->pagination->totalCount,
            'results' => $results
        ];
    }
}
