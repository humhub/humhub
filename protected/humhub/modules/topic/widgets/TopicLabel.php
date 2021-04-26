<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\widgets;

use humhub\modules\content\models\ContentTag;
use humhub\modules\topic\models\Topic;
use humhub\widgets\Label;
use humhub\widgets\Link;
use yii\helpers\Html;

class TopicLabel extends Label
{
    /**
     * @param Topic $topic
     * @return $this
     */
    public static function forTopic(Topic $topic)
    {
        $link = Link::withAction('', 'topic.addTopic')->options(['data-topic-id' => $topic->id, 'data-topic-url' => $topic->getUrl()]);

        return static::light($topic->name)->sortOrder(20)->color($topic->color)->withLink($link)->icon('fa-star');
    }

}
