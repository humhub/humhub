<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\topic\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\topic\models\Topic;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\menu\MenuLink;
use Yii;

/**
 * The "Topics" entry of a content's context menu
 * ({@see \humhub\modules\content\widgets\WallEntryControls}), opening the topic dialog.
 *
 * A menu entry, not a widget, since 1.20.
 */
class ContentTopicButton extends MenuLink
{
    /**
     * @var ContentActiveRecord
     */
    public $record;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->record->content->getStateService()->isDeleted()) {
            $this->setIsVisible(false);
            return;
        }

        $this->setId('topics');
        $this->setLink(
            Link::modal(Yii::t('TopicModule.base', 'Topics'))
                ->icon(Topic::getIcon())
                ->load(['/topic/content-topic', 'contentId' => $this->record->content->id]),
        );
    }
}
