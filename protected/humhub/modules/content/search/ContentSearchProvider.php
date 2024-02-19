<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\search;

use humhub\interfaces\SearchProviderInterface;
use humhub\modules\content\Module;
use Yii;
use yii\helpers\Url;

/**
 * ContentSearchProvider
 *
 * @author luke
 * @since 1.16
 */
class ContentSearchProvider implements SearchProviderInterface
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
        return Yii::t('ContentModule.base', 'Content');
    }

    /**
     * @inheritdoc
     */
    public function getAllResultsUrl(): string
    {
        return Url::to(['/content/search', 'keyword' => $this->keyword]);
    }

    /**
     * @inheritdoc
     */
    public function search(): void
    {
        if ($this->keyword === null) {
            return;
        }

        /* @var Module $module */
        $module = Yii::$app->getModule('content');

        $resultSet = $module->getSearchDriver()->search(new SearchRequest([
            'keyword' => $this->keyword,
            'pageSize' => $this->pageSize
        ]));

        $this->totalCount = $resultSet->pagination->totalCount;

        $this->results = [];
        foreach ($resultSet->results as $content) {
            $this->results[] = new SearchRecord($content);
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
