<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\search;

use humhub\components\listing\ListContext;
use humhub\interfaces\MetaSearchProviderInterface;
use humhub\modules\user\components\UserList;
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
    public string|array|null $route = '/user/people';

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
    public function getSortOrder(): int
    {
        return 200;
    }

    /**
     * @inheritdoc
     */
    public function getRoute(): string|array
    {
        return $this->route;
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
        // The directory's search (with the restrictions modules apply to it), whose "Show all
        // results" is the directory itself.
        $query = (new UserList())->build(
            // At most as long as the list's search takes: a longer keyword is cut, not refused.
            ['q' => mb_substr((string)$this->getKeyword(), 0, UserList::MAX_SEARCH_LENGTH)],
            ListContext::forCurrentUser(UserList::PURPOSE_DIRECTORY),
        )->query();

        $results = [];
        foreach ((clone $query)->limit($maxResults)->all() as $user) {
            $results[] = Yii::createObject(SearchRecord::class, [$user]);
        }

        return [
            'totalCount' => (int)$query->count(),
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
