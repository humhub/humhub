<?php

namespace humhub\modules\content\services;

use humhub\modules\content\interfaces\Searchable;
use humhub\modules\content\jobs\SearchDeleteDocument;
use humhub\modules\content\jobs\SearchUpdateDocument;
use humhub\modules\content\models\Content;
use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\content\search\driver\ZendLucenceDriver;
use Yii;

class ContentSearchService
{
    public function getDriver(): AbstractDriver
    {
        return new ZendLucenceDriver();
    }

    public function updateContent(Content $content): void
    {
        if ($content->getModel() instanceof Searchable && (new ContentStateService(['content' => $content]))->isPublished()) {
            Yii::$app->queue->push(new SearchUpdateDocument(['contentId' => $content->id]));
        } else {
            $this->deleteContent($content);
        }
    }

    public function deleteContent(Content $content): void
    {
        Yii::$app->queue->push(new SearchDeleteDocument(['contentId' => $content->id]));
    }
}
