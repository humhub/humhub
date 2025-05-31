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
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\bootstrap\Link;

/**
 * @since 1.18
 */
class TopicBadge extends Badge
{
    /**
     * @param Topic $topic
     * @return $this
     * @throws \Throwable
     */
    public static function forTopic(Topic $topic, ?ContentContainerActiveRecord $contentContainer = null): static
    {
        $badge = static::instance($topic->name, $topic->color)
            ->sortOrder(20)
            ->icon('star');

        if ($contentContainer = $contentContainer ?: ContentContainerHelper::getCurrent()) {
            $badge->withLink(Link::withAction('', 'topic.addTopic')->options([
                'data-topic-id' => $topic->id,
                'data-topic-url' => $topic->getUrl($contentContainer),
            ]));
        }

        return $badge;
    }

}
