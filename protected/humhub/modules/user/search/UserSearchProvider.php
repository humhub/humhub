<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\search;

use humhub\interfaces\SearchProviderInterface;
use humhub\modules\user\components\PeopleQuery;
use Yii;
use yii\helpers\Url;

/**
 * UserSearchProvider
 *
 * @author luke
 * @since 1.16
 */
class UserSearchProvider implements SearchProviderInterface
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
        return Yii::t('UserModule.base', 'Profile');
    }

    /**
     * @inheritdoc
     */
    public function getAllResultsUrl(): string
    {
        return Url::to(['/people', 'keyword' => $this->keyword]);
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
