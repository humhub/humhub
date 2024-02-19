<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\search;

use humhub\interfaces\SearchProviderInterface;
use humhub\modules\space\components\SpaceDirectoryQuery;
use Yii;
use yii\helpers\Url;

/**
 * SpaceSearchProvider
 *
 * @author luke
 * @since 1.16
 */
class SpaceSearchProvider implements SearchProviderInterface
{
    public ?string $keyword = null;
    public int $pageSize = 4;

    protected ?int $totalCount = null;

    /**
     * @var SearchProviderInterface[]|null
     */
    protected ?array $results = null;

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
    public function getAllResultsUrl(): string
    {
        return Url::to(['/spaces', 'keyword' => $this->keyword]);
    }

    /**
     * @inheritdoc
     */
    public function search(): void
    {
        if ($this->keyword === null) {
            return;
        }

        $spaceDirectoryQuery = new SpaceDirectoryQuery([
            'defaultFilters' => ['keyword' => $this->keyword],
            'pageSize' => $this->pageSize
        ]);

        $this->totalCount = $spaceDirectoryQuery->pagination->totalCount;

        $this->results = [];
        foreach ($spaceDirectoryQuery->all() as $space) {
            $this->results[] = new SearchRecord($space);
        }
    }

    /**
     * @inheritdoc
     */
    public function isSearched(): bool
    {
        return $this->results !== null;
    }

    /**
     * @inheritdoc
     */
    public function getTotal(): int
    {
        return isset($this->totalCount) ? (int) $this->totalCount : 0;
    }

    /**
     * @inheritdoc
     */
    public function getRecords(): array
    {
        return $this->results ?? [];
    }
}
