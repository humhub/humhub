<?php

namespace humhub\modules\content\jobs;

use humhub\modules\content\models\Content;
use humhub\modules\content\services\ContentSearchService;
use humhub\modules\queue\ActiveJob;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use Yii;

class SearchUpdateDocument extends ActiveJob implements ExclusiveJobInterface
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
        $content = Content::findOne(['id' => $this->contentId]);
        if ($content) {
            (new ContentSearchService())->getDriver()->update($content);
        }
    }

}
