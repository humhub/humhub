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
}
