<?php

namespace humhub\modules\content\widgets;

/**
 * WallEntryLinksWidget is an instance of StackWidget.
 *
 * Display some links below a wall entry. Allows modules to add own links to
 * the wall entry.
 *
 * @package humhub.modules_core.wall.widgets
 * @since 0.5
 */
class WallEntryLinks extends \humhub\widgets\BaseStack
{

    /**
     * Object derived from HActiveRecordContent
     *
     * @var type
     */
    public $object = null;

}

?>
