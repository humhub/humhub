<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\widgets\menu\MenuLink;
use Yii;

/**
 * The "Move content" entry of a content's context menu ({@see WallEntryControls}).
 *
 * A menu entry, not a widget, since 1.20 - see {@see WallEntryControls::createEntry()}.
 */
class MoveContentLink extends MenuLink
{
    /**
     * @var ContentActiveRecord
     */
    public $model;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $content = $this->model->content;

        // Shown when the user may move content within the container in general, not
        // considering the other checks of moving this particular content.
        if (!$content->container || !$content->checkMovePermission()) {
            $this->setIsVisible(false);
            return;
        }

        $this->setLabel(Yii::t('ContentModule.base', 'Move content'));
        $this->setIcon('arrows-horizontal');
        $this->setUrl('#');
        $this->setHtmlOptions([
            'data-action-click' => 'ui.modal.load',
            'data-action-url' => $content->container->createUrl('/content/move/move', ['id' => $content->id]),
        ]);
    }
}
