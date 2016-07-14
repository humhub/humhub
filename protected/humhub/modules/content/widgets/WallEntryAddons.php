<?php

namespace humhub\modules\content\widgets;

/**
 * WallEntryAddonWidget is an instance of StackWidget for wall entries.
 *
 * This widget is used to add some widgets to a wall entry.
 * e.g. Likes or Comments.
 *
 * @package humhub.modules_core.wall.widgets
 */
class WallEntryAddons extends \humhub\widgets\BaseStack
{

    /**
     * Object derived from HActiveRecordContent
     *
     * @var type
     */
    public $object = null;

}

?>
