<?php

namespace humhub\modules\content\widgets;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\widgets\BaseStack;

/**
 * WallEntryLinksWidget is an instance of StackWidget.
 *
 * Display some links below a wall entry. Allows modules to add own links to
 * the wall entry.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 */
class WallEntryLinks extends BaseStack
{
    /**
     * @var ContentActiveRecord
     */
    public $object = null;

    /**
     * @inheritdoc
     */
    public $seperator = '&nbsp;&middot;&nbsp;';

    /**
     * @inheritdoc
     */
    public $template = '<div class="wall-entry-controls wall-entry-links">{content}</div>';

}
