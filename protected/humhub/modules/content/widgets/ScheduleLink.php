<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\widgets\menu\MenuLink;
use Yii;

/**
 * The "Schedule publication" entry of a content's context menu ({@see WallEntryControls}).
 *
 * A menu entry, not a widget, since 1.20 - see {@see WallEntryControls::createEntry()}.
 *
 * @since 1.14
 */
class ScheduleLink extends MenuLink
{
    public ContentActiveRecord $contentRecord;

    public array $allowedStates = [Content::STATE_DRAFT, Content::STATE_SCHEDULED];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $content = $this->contentRecord->content;
        $container = $content->container;

        if (!in_array($content->state, $this->allowedStates)
            || !$container instanceof ContentContainerActiveRecord
            || !$content->canEdit()) {
            $this->setIsVisible(false);
            return;
        }

        $this->setLabel(Yii::t('ContentModule.base', 'Schedule publication'));
        $this->setIcon('clock-o');
        $this->setUrl('#');
        $this->getLink()->action(
            'scheduleOptions',
            $container->createUrl('/content/content/schedule-options', ['id' => $content->id]),
        );
    }
}
