<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\search;

use humhub\interfaces\MetaSearchProviderInterface;
use humhub\modules\user\components\PeopleQuery;
use humhub\services\MetaSearchService;
use Yii;

/**
 * User Meta Search Provider
 *
 * @author luke
 * @since 1.16
 */
class UserSearchProvider implements MetaSearchProviderInterface
{
    private ?MetaSearchService $service = null;
    public ?string $keyword = null;

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
    public function getRoute(): string
    {
        return '/user/people';
    }

    /**
     * @inheritdoc
     */
    public function getAllResultsText(): string
    {
        return $this->getService()->hasResults()
            ? Yii::t('base', 'Show all results')
            : Yii::t('UserModule.base', 'Advanced Profile Search');
    }

    /**
     * @inheritdoc
     */
    public function getIsHiddenWhenEmpty(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResults(int $maxResults): array
    {
        $peopleQuery = new PeopleQuery([
            'defaultFilters' => ['keyword' => $this->getKeyword()],
            'pageSize' => $maxResults,
        ]);

        $results = [];
        foreach ($peopleQuery->all() as $user) {
            $results[] = new SearchRecord($user);
        }

        return [
            'totalCount' => $peopleQuery->pagination->totalCount,
            'results' => $results,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getService(): MetaSearchService
    {
        if ($this->service === null) {
            $this->service = new MetaSearchService($this);
        }

        return $this->service;
    }

    /**
     * @inheritdoc
     */
    public function getKeyword(): ?string
    {
        return $this->keyword;
    }
}
