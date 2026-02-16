<?php

namespace humhub\modules\content\services;

use humhub\modules\comment\models\Comment;
use humhub\modules\content\models\Content;
use humhub\modules\file\converter\TextConverter;
use humhub\modules\file\models\File;
use humhub\modules\user\models\User;
use Yii;
use yii\caching\TagDependency;

class ContentSearchService
{
    public const CACHE_TAG = 'search-content';

    public function __construct(public Content $content)
    {
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
        // ToDo: Add related files
        // ToDo: Add comments in correct order (add parents below

        $result = '';
        foreach (Comment::findAll(['content_id' => $this->content->id]) as $comment) {
            $result .= "\n\n" . $comment->message . "\n\n";
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

    public static function flushCache(): void
    {
        TagDependency::invalidate(Yii::$app->cache, self::CACHE_TAG);
    }
}
