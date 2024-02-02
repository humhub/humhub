<?php

namespace humhub\modules\content\services;

use humhub\modules\activity\models\Activity;
use humhub\modules\comment\models\Comment;
use humhub\modules\content\jobs\SearchDeleteDocument;
use humhub\modules\content\jobs\SearchUpdateDocument;
use humhub\modules\content\models\Content;
use humhub\modules\content\Module;
use humhub\modules\content\search\driver\AbstractDriver;
use humhub\modules\file\converter\TextConverter;
use humhub\modules\file\models\File;
use Yii;

class ContentSearchService
{
    public Content $content;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    public function update($asActiveJob = true): void
    {
        if (!$this->isIndexable()) {
            return;
        }

        if ((new ContentStateService(['content' => $this->content]))->isPublished()) {
            if ($asActiveJob) {
                Yii::$app->queue->push(new SearchUpdateDocument(['contentId' => $this->content->id]));
            } else {
                $this->getSearchDriver()->update($this->content);
            }
        } else {
            $this->delete($asActiveJob);
        }
    }

    public function delete($asActiveJob = true): void
    {
        if (!$this->isIndexable()) {
            return;
        }
        if ($asActiveJob) {
            Yii::$app->queue->push(new SearchDeleteDocument(['contentId' => $this->content->id]));
        } else {
            $this->getSearchDriver()->delete($this->content);
        }
    }

    public function getFileContentAsText(): string
    {
        $result = '';
        $textConverter = new TextConverter();

        foreach (File::findAll(['object_model' => $this->content->object_model, 'object_id' => $this->content->object_id]) as $file) {
            if ($textConverter->applyFile($file)) {
                $result .= $file->file_name . ': ' . $textConverter->getContentAsText() . "\n\n\n\n";
            }
        }

        return $result;
    }

    public function getCommentsAsText(): string
    {
        $result = '';
        foreach (Comment::findAll(['object_model' => $this->content->object_model, 'object_id' => $this->content->object_id]) as $comment) {
            $result .= $comment->user->getDisplayName() . ': ' . $comment->message . "\n\n";
            // ToDo: Add related files
        }
        return $result;
    }

    public function isIndexable()
    {
        return !($this->content->object_model === Activity::class);
    }

    private function getSearchDriver(): AbstractDriver
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('content');
        return $module->getSearchDriver();
    }

}
