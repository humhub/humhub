<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\widgets;

use humhub\modules\topic\models\Topic;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\Link;

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
    public static function forTopic(Topic $topic): static
    {
        $link = Link::withAction('', 'topic.addTopic')->options(['data-topic-id' => $topic->id, 'data-topic-url' => $topic->getUrl()]);

        return static::instance($topic->name, $topic->color)->sortOrder(20)->withLink($link)->icon('star');
    }

}
