<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\jobs;

use humhub\modules\content\models\Content;
use humhub\modules\content\services\ContentSearchService;
use humhub\modules\content\services\SearchJobService;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\queue\LongRunningActiveJob;
use Yii;

class SearchRebuildIndex extends LongRunningActiveJob implements ExclusiveJobInterface
{
    /**
     * @inhertidoc
     */
    public function getExclusiveJobId()
    {
        return 'content-search.rebuild-index';
    }

    /**
     * @inhertidoc
     */
    public function run()
    {
        return $this->getService()->run(function (): void {
            foreach (Content::find()->each() as $content) {
                (new ContentSearchService($content))->update(false);
            }

            Yii::$app->cache->flush();
        });
    }

    /**
     * @inheritdoc
     */
    public function canRetry($attempt, $error)
    {
        return $this->getService()->canRetry($attempt);
    }

    public function getService(): SearchJobService
    {
        return new SearchJobService();
    }
}
