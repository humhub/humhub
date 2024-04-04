<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\jobs;

use humhub\modules\content\models\Content;
use humhub\modules\content\services\ContentSearchService;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\queue\LongRunningActiveJob;

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
        foreach (Content::find()->each() as $content) {
            (new ContentSearchService($content))->update(false);
        }
    }
}
