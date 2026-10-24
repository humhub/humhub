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
 * The "Permalink" entry of a content's context menu ({@see WallEntryControls}).
 *
 * A menu entry, not a widget, since 1.20 - see {@see WallEntryControls::createEntry()}.
 *
 * @since 0.5
 */
class PermaLink extends MenuLink
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

        $this->setLabel(Yii::t('ContentModule.base', 'Permalink'));
        $this->setIcon('link');
        $this->setUrl('#');
        $this->setHtmlOptions([
            'data-action-click' => 'content.permalink',
            'data-content-permalink' => Url::to(['/content/perma', 'id' => $this->content->content->id], true),
        ]);
    }
}
