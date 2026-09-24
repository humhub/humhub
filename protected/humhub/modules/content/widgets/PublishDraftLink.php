<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\widgets\menu\MenuLink;
use Yii;
use yii\helpers\Url;

/**
 * The "Publish draft" entry of a content's context menu ({@see WallEntryControls}).
 *
 * A menu entry, not a widget, since 1.20 - see {@see WallEntryControls::createEntry()}.
 */
class PublishDraftLink extends MenuLink
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

        if (!$content->getStateService()->isDraft() || !$content->canEdit()) {
            $this->setIsVisible(false);
            return;
        }

        $this->setLabel(Yii::t('ContentModule.base', 'Publish draft'));
        $this->setIcon('arrow-back-up-double');
        $this->setUrl('#');
        $this->setHtmlOptions([
            'data-action-click' => 'publishDraft',
            'data-action-url' => Url::to(['/content/content/publish-draft', 'id' => $content->id]),
        ]);
    }
}
