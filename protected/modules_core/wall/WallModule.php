<?php

/**
 * The Wall Module provides all wall/stream functions inside the application.
 *
 * Walls/Streams are used to display contents / activities in a automatically
 * reloading list.
 *
 * @package humhub.modules_core.wall
 * @since 0.5
 * @author Luke
 */
class WallModule extends HWebModule
{

    public $isCoreModule = true;

    /**
     * Inits the wall module
     */
    public function init()
    {

        $this->setImport(array(
            'wall.controllers.*',
            'wall.models.*',
            'wall.behaviors.*',
        ));
    }

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

        // Add Delete Link
        $event->sender->addWidget('application.modules_core.wall.widgets.DeleteLinkWidget', array(
            'object' => $event->sender->object
                )
        );

        // Add Edit Link
        $event->sender->addWidget('application.modules_core.wall.widgets.EditLinkWidget', array(
            'object' => $event->sender->object
                )
        );

        // Add Notifications on/off Link
        $event->sender->addWidget('application.modules_core.wall.widgets.NotificationSwitchLinkWidget', array(
            'content' => $event->sender->object
                )
        );
        // Add Perma Link
        $event->sender->addWidget('application.modules_core.wall.widgets.PermaLinkWidget', array(
            'content' => $event->sender->object
                )
        );

        // Add Stick Link
        $event->sender->addWidget('application.modules_core.wall.widgets.StickLinkWidget', array(
            'content' => $event->sender->object
                )
        );

        // Add Archive Link
        $event->sender->addWidget('application.modules_core.wall.widgets.ArchiveLinkWidget', array(
            'content' => $event->sender->object
                )
        );
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
