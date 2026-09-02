<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\widgets\WallEntryControlLink;
use humhub\modules\topic\models\Topic;
use humhub\modules\ui\menu\MenuLink;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\bootstrap\Link;
use Yii;

class ContentTopicButton extends WallEntryControlLink
{
    /**
     * @var ContentActiveRecord
     */
    public $record;

    public function renderLink()
    {
        if ($this->record->content->getStateService()->isDeleted()) {
            return '';
        }

        return $this->buildLink();
    }

    /**
     * @inheritdoc
     *
     * Overridden because this widget builds its label, icon and modal action inside
     * {@see self::renderLink()} rather than from the `$label`/`$icon`/`$action` properties the
     * base class describes — which is exactly the case the base class refuses to describe.
     * Both paths run {@see self::buildLink()}, so the described entry and the rendered anchor
     * cannot drift apart.
     *
     * @since 1.20
     */
    public function describeMenuEntry(): ?array
    {
        if ($this->record->content->getStateService()->isDeleted()) {
            return null;
        }

        $link = $this->buildLink();

        return [
            'id' => 'topics',
            'label' => (string)$link->label,
            'icon' => MenuLink::describeIcon($link->icon),
            'htmlOptions' => $link->options,
        ];
    }

    /**
     * The one definition of this entry's link, shared by rendering and describing.
     *
     * @since 1.20
     */
    protected function buildLink(): Button
    {
        return Link::modal(Yii::t('TopicModule.base', 'Topics'))
            ->icon(Topic::getIcon())
            ->load(['/topic/content-topic', 'contentId' => $this->record->content->id])
            ->options($this->options);
    }
}
