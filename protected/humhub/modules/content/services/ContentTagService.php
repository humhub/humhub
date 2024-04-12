<?php

namespace humhub\modules\content\services;

use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentTag;
use humhub\modules\content\models\ContentTagRelation;
use Yii;
use yii\base\InvalidArgumentException;

class ContentTagService
{
    private Content $content;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    /**
     * Adds the given ContentTag array to this content.
     *
     * @param $tags ContentTag[]
     * @since 1.3
     */
    public function addTags($tags): void
    {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
    }

    /**
     * Adds a new ContentTagRelation for this content and the given $tag instance.
     *
     * @param ContentTag $tag
     * @return bool if the provided tag is part of another ContentContainer
     * @since 1.2.2
     */
    public function addTag(ContentTag $tag)
    {
        if (!empty($tag->contentcontainer_id) && $tag->contentcontainer_id != $this->content->contentcontainer_id) {
            throw new InvalidArgumentException(
                Yii::t('ContentModule.base', 'Content Tag with invalid contentcontainer_id assigned.'),
            );
        }

        if (ContentTagRelation::findBy($this->content, $tag)->count()) {
            return true;
        }

        $this->content->refresh();

        (new ContentSearchService($this->content))->update();

        $contentRelation = new ContentTagRelation($this->content, $tag);

        return $contentRelation->save();
    }
}
