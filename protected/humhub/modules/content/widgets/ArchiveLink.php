<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\widgets\menu\MenuLink;
use Yii;
use yii\helpers\Url;

/**
 * The "Move to archive" / "Unarchive" entry of a content's context menu
 * ({@see WallEntryControls}).
 *
 * A menu entry, not a widget, since 1.20 - see {@see WallEntryControls::createEntry()}.
 *
 * @since 0.5
 */
class ArchiveLink extends MenuLink
{
    /**
     * @var ContentActiveRecord
     */
    public $content;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $content = $this->content->content;

        if (!$content->canArchive()) {
            $this->setIsVisible(false);
            return;
        }

        $archived = $content->isArchived();

        $this->setLabel($archived
            ? Yii::t('ContentModule.base', 'Unarchive')
            : Yii::t('ContentModule.base', 'Move to archive'));
        $this->setIcon('archive');
        $this->setUrl('#');
        $this->setHtmlOptions([
            'data-action-click' => $archived ? 'unarchive' : 'archive',
            'data-action-url' => Url::to([$archived ? '/content/content/unarchive' : '/content/content/archive', 'id' => $content->id]),
        ]);
    }
}
