<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\jobs;

use humhub\modules\content\models\Content;
use humhub\modules\content\services\ContentSearchService;
use humhub\modules\content\services\SearchJobService;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\queue\LongRunningActiveJob;

class SearchUpdateDocument extends LongRunningActiveJob implements ExclusiveJobInterface
{
    public $contentId;

    /**
     * @inhertidoc
     */
    public function getExclusiveJobId()
    {
        return 'content-search.update.' . $this->contentId;
    }

    /**
     * @inhertidoc
     */
    public function run()
    {
        return $this->getService()->run(function () {
            $content = Content::findOne(['id' => $this->contentId]);
            if ($content) {
                (new ContentSearchService($content))->update(false);
            }
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
