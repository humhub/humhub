<?php

namespace humhub\modules\content\services;

use humhub\modules\comment\models\Comment;
use humhub\modules\content\models\Content;
use humhub\modules\file\converter\TextConverter;
use humhub\modules\file\models\File;
use humhub\modules\user\models\User;
use Yii;

class ContentSearchService
{
    public Content $content;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    public function update(bool $asActiveJob = true): void
    {
        if (!$this->isIndexable()) {
            return;
        }

        if ((new ContentStateService(['content' => $this->content]))->isPublished()) {
            (new SearchDriverService())->update($this->content, $asActiveJob);
        } else {
            $this->delete($asActiveJob);
        }
    }

    public function delete(bool $asActiveJob = true): void
    {
        (new SearchDriverService())->delete($this->content->id, $asActiveJob);
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
            $result .= "\n\n" . $comment->message . "\n\n";
            foreach (Comment::findAll(['object_model' => Comment::class, 'object_id' => $comment->id]) as $subComment) {
                $result .= "\n\n" . $subComment->message . "\n\n";
            }
            // ToDo: Add related files
        }
        return $result;
    }

    public function isIndexable(): bool
    {
        if (empty($this->content->id)) {
            return false;
        }

        if ($this->content->stream_channel !== Content::STREAM_CHANNEL_DEFAULT) {
            return false;
        }

        if (!Yii::$app->getModule('stream')->showDeactivatedUserContent) {
            $author = $this->content->createdBy;
            return $author && $author->status === User::STATUS_ENABLED;
        }

        return true;
    }
}
