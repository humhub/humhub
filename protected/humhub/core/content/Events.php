<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\core\content;

use humhub\core\content\models\Content;

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

        $integrityChecker->showTestHeadline("Wall Module (" . models\WallEntry::find()->count() . " entries)");
        foreach (models\WallEntry::find()->joinWith('content')->all() as $w) {
            if ($w->content === null) {
                if ($integrityChecker->showFix("Deleting wall entry id " . $w->id . " without assigned wall entry!")) {
                    $w->delete();
                }
            }
        }

        //TODO: Maybe not the best place for that
        $integrityChecker->showTestHeadline("Content Objects (" . Content::find()->count() . " entries)");
        foreach (Content::find()->all() as $content) {
            if ($content->user == null) {
                if ($integrityChecker->showFix("Deleting content id " . $content->id . " of type " . $content->object_model . " without valid user!")) {
                    $content->delete();
                }
            }
            if ($content->getUnderlyingObject() == null) {
                if ($integrityChecker->showFix("Deleting content id " . $content->id . " of type " . $content->object_model . " without valid content object!")) {
                    $content->delete();
                }
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
        $event->sender->addWidget(widgets\WallEntryLinks::className(), array(
            'object' => $event->sender->object,
            'seperator' => "&nbsp;&middot;&nbsp;",
            'template' => '<div class="wall-entry-controls">{content}</div>',
                ), array('sortOrder' => 10)
        );
    }

}
