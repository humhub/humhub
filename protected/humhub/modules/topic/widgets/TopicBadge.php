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

/**
 * @since 1.18
 */
class TopicBadge extends Badge
{
    /**
     * @param Topic $topic
     * @return $this
     * @throws \Throwable
     * @deprecated since 1.18.0-beta.7 use static::topic($topic, $content)
     */
    public static function forTopic(Topic $topic, ?ContentContainerActiveRecord $contentContainer = null): static
    {
        return static::topic($topic);
    }

    public static function topic(Topic $topic, ?Content $content = null): static
    {
        return static::none()->setTopic($topic, $content);
    }

    public function setTopic(Topic $topic, ?Content $content = null): static
    {
        $this->setLabel($topic->name);
        $this->sortOrder($topic->sort_order);

        if ($this->icon === null) {
            $this->icon('star');
        }

        if (!isset($this->options['class'][0])) {
            // Set outline badge style by default only when it is not defined before
            $this->cssClass(['border', 'border-secondary', 'text-secondary', 'bg-transparent']);
        }

        $contentContainer = $content instanceof Content ? $content->container : ContentContainerHelper::getCurrent();

        if ($contentContainer) {
            $urlParams = [];
            if ($content instanceof Content && $content->hidden) {
                $urlParams['filters[entry_hidden]'] = 1;
                $urlParams['filters_visible'] = 1;
            }

            $this->withLink(Link::withAction('', 'topic.addTopic')->options([
                'data-topic-id' => $topic->id,
                'data-topic-url' => $topic->getUrl($contentContainer, $urlParams),
            ]));
        }

        return $this;
    }
}
