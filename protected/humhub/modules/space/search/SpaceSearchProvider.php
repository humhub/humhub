<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\search;

use humhub\components\SearchProvider;
use humhub\modules\space\components\SpaceDirectoryQuery;
use Yii;

/**
 * SpaceSearchProvider
 *
 * @author luke
 * @since 1.16
 */
class SpaceSearchProvider extends SearchProvider
{
    public bool $showOnEmpty = false;
    protected ?string $route = '/spaces';

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return Yii::t('SpaceModule.base', 'Spaces');
    }

    /**
     * @inheritdoc
     */
    public function getAllResultsText(): string
    {
        return $this->hasRecords()
            ? Yii::t('base', 'Show all results')
            : Yii::t('SpaceModule.base', 'Advanced Spaces Search');
    }

    /**
     * @inheritdoc
     */
    public function searchResults(): array
    {
        $spaceDirectoryQuery = new SpaceDirectoryQuery([
            'defaultFilters' => ['keyword' => $this->keyword],
            'pageSize' => $this->pageSize
        ]);

        $this->totalCount = $spaceDirectoryQuery->pagination->totalCount;

        $results = [];
        foreach ($spaceDirectoryQuery->all() as $space) {
            $results[] = new SearchRecord($space);
        }

        return $results;
    }
}
