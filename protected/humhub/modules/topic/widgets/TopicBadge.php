<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\topic\widgets;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\content\models\Content;
use humhub\modules\topic\models\Topic;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\bootstrap\Link;
use Yii;

/**
 * @since 1.18
 */
class TopicBadge extends Badge
{
    /**
     * Get a badge instance for the given topic
     *
     * @param Topic $topic
     * @param Content|ContentContainerActiveRecord|null $content Recommended to provide Content for proper topic URL,
     *                (Type ContentContainerActiveRecord is deprecated since 1.18.0-beta.7)
     * @return static
     */
    public static function forTopic(Topic $topic, Content|ContentContainerActiveRecord|null $content = null): static
    {
        $badge = static::none($topic->name)
            ->sortOrder($topic->sort_order)
            ->icon('star')
            ->cssClass(['border', 'border-secondary', 'text-secondary', 'bg-transparent']);

        $urlParams = [];

        if ($content instanceof Content) {
            $contentContainer = $content->container;
            if ($content->hidden) {
                $urlParams['filters[entry_hidden]'] = 1;
                $urlParams['filters_visible'] = 1;
            }
        } elseif ($content instanceof ContentContainerActiveRecord) {
            $contentContainer = $content;
            Yii::warning('Deprecated type ContentContainerActiveRecord for the second param TopicBadge::forTopic()', 'content');
        } else {
            $contentContainer = ContentContainerHelper::getCurrent();
        }

        if ($contentContainer instanceof ContentContainerActiveRecord) {
            $badge->withLink(Link::withAction('', 'topic.addTopic')->options([
                'data-topic-id' => $topic->id,
                'data-topic-url' => $topic->getUrl($contentContainer, $urlParams),
            ]));
        }

        return $badge;
    }
}
