<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\widgets\menu\MenuLink;
use Yii;
use yii\helpers\Url;

/**
 * The "Lock comments" / "Unlock comments" entry of a content's context menu
 * ({@see WallEntryControls}).
 *
 * A menu entry, not a widget, since 1.20 - see {@see WallEntryControls::createEntry()}.
 *
 * @since 1.10
 */
class LockCommentsLink extends MenuLink
{
    /**
     * @var ContentActiveRecord
     */
    public $contentRecord;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $content = $this->contentRecord->content;

        if (!$content->canLockComments()) {
            $this->setIsVisible(false);
            return;
        }

        $locked = $content->isLockedComments();

        $this->setLabel($locked
            ? Yii::t('ContentModule.base', 'Unlock comments')
            : Yii::t('ContentModule.base', 'Lock comments'));
        $this->setIcon($locked ? 'message-circle' : 'message-circle-filled');
        $this->setUrl('#');
        $this->setHtmlOptions([
            'data-action-click' => $locked ? 'unlockComments' : 'lockComments',
            'data-action-url' => Url::to([$locked ? '/content/content/unlock-comments' : '/content/content/lock-comments', 'id' => $content->id]),
        ]);
    }
}
