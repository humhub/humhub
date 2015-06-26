<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\core\content;

/**
 * Description of Events
 *
 * @author luke
 */
class Events extends \yii\base\Object
{

    /**
     * On run of integrity check command, validate all wall data
     *
     * @param type $event
     */
    public static function onIntegrityCheck($event)
    {

        $integrityChecker = $event->sender;

        $integrityChecker->showTestHeadline("Validating Wall Module (" . WallEntry::model()->count() . " entries)");
        foreach (WallEntry::model()->with('content')->findAll() as $w) {
            if ($w->content === null) {
                $integrityChecker->showFix("Deleting wall entry id " . $w->id . " without assigned wall entry!");
                if (!$integrityChecker->simulate)
                    $w->delete();
                continue;
            }
        }

        //TODO: Maybe not the best place for that
        $integrityChecker->showTestHeadline("Validating Content Objects (" . Content::model()->count() . " entries)");
        foreach (Content::model()->findAll() as $content) {
            if ($content->user == null) {
                $integrityChecker->showFix("Deleting content id " . $content->id . " of type " . $content->object_model . " without valid user!");
                if (!$integrityChecker->simulate)
                    $content->delete();
                continue;
            }
            if ($content->getUnderlyingObject() == null) {
                $integrityChecker->showFix("Deleting content id " . $content->id . " of type " . $content->object_model . " without valid content object!");
                if (!$integrityChecker->simulate)
                    $content->delete();
                continue;
            }
        }
    }

    /**
     * On init of WallEntryControlsWidget add some default widgets to it.
     *
     * @param CEvent $event
     */
    public static function onWallEntryControlsInit($event)
    {
        $stackWidget = $event->sender;
        $content = $event->sender->object;

        $stackWidget->addWidget(widgets\DeleteLink::className(), ['content' => $content]);
        $stackWidget->addWidget(widgets\EditLink::className(), ['content' => $content]);
        //$stackWidget->addWidget(widgets\NotificationSwitchLink::className(), ['content' => $content]);
        $stackWidget->addWidget(widgets\PermaLink::className(), ['content' => $content]);
        $stackWidget->addWidget(widgets\ArchiveLink::className(), ['content' => $content]);
    }

    /**
     * On init of the WallEntryAddonWidget, attach the wall entry links widget.
     *
     * @param CEvent $event
     */
    public static function onWallEntryAddonInit($event)
    {

        $event->sender->addWidget('application.modules_core.wall.widgets.WallEntryLinksWidget', array(
            'object' => $event->sender->object,
            'seperator' => "&nbsp;&middot;&nbsp;",
            'template' => '<div class="wall-entry-controls">{content}</div>',
                ), array('sortOrder' => 10)
        );
    }

}
