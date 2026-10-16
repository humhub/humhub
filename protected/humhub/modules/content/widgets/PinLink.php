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
 * The "Pin to top" / "Unpin" entry of a content's context menu ({@see WallEntryControls}).
 *
 * A menu entry, not a widget, since 1.20 - see {@see WallEntryControls::createEntry()}.
 *
 * @since 0.5
 */
class PinLink extends MenuLink
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

        if (!$content->canPin()) {
            $this->setIsVisible(false);
            return;
        }

        $pinned = $content->isPinned();

        $this->setLabel($pinned
            ? Yii::t('ContentModule.base', 'Unpin')
            : Yii::t('ContentModule.base', 'Pin to top'));
        $this->setIcon('map-pin');
        $this->setUrl('#');
        $this->getLink()->action(
            $pinned ? 'unpin' : 'pin',
            Url::to([$pinned ? '/content/content/un-pin' : '/content/content/pin', 'id' => $content->id]),
        );
    }
}
