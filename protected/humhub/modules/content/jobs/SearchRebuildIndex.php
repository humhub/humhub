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
     * Number of processed items between interim progress log messages.
     */
    public const LOG_PROGRESS_INTERVAL = 100;

    /**
     * Log category used for all search index rebuild messages.
     */
    public const LOG_CATEGORY = 'search-indexing';

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
            $pid = getmypid() ?: 'unknown';

            Yii::warning(sprintf('Search index rebuild [PID %s] started.', $pid), self::LOG_CATEGORY);

            $processed = 0;
            foreach (Content::find()->each() as $content) {
                (new ContentSearchService($content))->update(false);

                if (++$processed % self::LOG_PROGRESS_INTERVAL === 0) {
                    Yii::warning(sprintf('Search index rebuild [PID %s] processed: %d items.', $pid, $processed), self::LOG_CATEGORY);
                }
            }

            ContentSearchService::flushCache();

            Yii::warning(sprintf('Search index rebuild [PID %s] finished. %d items indexed.', $pid, $processed), self::LOG_CATEGORY);
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
