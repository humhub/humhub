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
    protected ?string $route = '/people';

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return Yii::t('UserModule.base', 'Profile');
    }

    /**
     * @inheritdoc
     */
    public function search(): void
    {
        if ($this->keyword === null) {
            return;
        }

        $peopleQuery = new PeopleQuery([
            'defaultFilters' => ['keyword' => $this->keyword],
            'pageSize' => $this->pageSize
        ]);

        $this->totalCount = $peopleQuery->pagination->totalCount;

        $this->results = [];
        foreach ($peopleQuery->all() as $user) {
            $this->results[] = new SearchRecord($user);
        }
    }
}
