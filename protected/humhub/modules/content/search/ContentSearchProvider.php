<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\search;

use humhub\components\SearchProvider;
use humhub\modules\content\Module;
use Yii;

/**
 * ContentSearchProvider
 *
 * @author luke
 * @since 1.16
 */
class ContentSearchProvider extends SearchProvider
{
    protected ?string $route = '/content/search';

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
    public function getAllResultsText(): string
    {
        return $this->hasRecords()
            ? Yii::t('base', 'Show all results')
            : Yii::t('ContentModule.base', 'Advanced Content Search');
    }

    /**
     * @inheritdoc
     */
    public function runSearch(): array
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('content');

        $resultSet = $module->getSearchDriver()->search(new SearchRequest([
            'keyword' => $this->keyword,
            'pageSize' => $this->pageSize
        ]));

        $results = [];
        foreach ($resultSet->results as $content) {
            $results[] = new SearchRecord($content);
        }

        return [
            'totalCount' => $resultSet->pagination->totalCount,
            'results' => $results
        ];
    }
}
