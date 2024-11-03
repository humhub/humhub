<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\widgets;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\topic\models\Topic;
use humhub\widgets\Label;
use humhub\widgets\Link;

class TopicLabel extends Label
{
    /**
     * @param Topic $topic
     * @return $this
     */
    public static function forTopic(Topic $topic, ?ContentContainerActiveRecord $contentContainer = null)
    {
        $label = static::light($topic->name)
            ->sortOrder(20)
            ->color($topic->color)
            ->icon('fa-star');

        if ($contentContainer = $contentContainer ?: ContentContainerHelper::getCurrent()) {
            $label->withLink(Link::withAction('', 'topic.addTopic')->options([
                'data-topic-id' => $topic->id,
                'data-topic-url' => $topic->getUrl($contentContainer),
            ]));
        }

        return $label;
    }

}
