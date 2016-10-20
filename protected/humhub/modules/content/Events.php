<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content;

use Yii;
use humhub\modules\content\models\Content;
use humhub\modules\content\components\MailUpdateSender;


/**
 * Events provides callbacks to handle events.
 * 
 * @author luke
 */
class Events extends \yii\base\Object
{

    public static function onUserDelete($event)
    {
        $user = $event->sender;

        models\WallEntry::deleteAll(['wall_id' => $user->wall_id]);
        models\Wall::deleteAll(['id' => $user->wall_id]);
        foreach (Content::findAll(['created_by' => $user->id]) as $content) {
            $content->delete();
        }
        return true;
    }

    public static function onSpaceDelete($event)
    {
        $space = $event->sender;

        models\WallEntry::deleteAll(['wall_id' => $space->wall_id]);
        models\Wall::deleteAll(['id' => $space->wall_id]);
        foreach (Content::findAll(['contentcontainer_id' => $space->contentContainerRecord->id]) as $content) {
            $content->delete();
        }

        return true;
    }

    /**
     * Callback to validate module database records.
     *
     * @param Event $event
     */
    public static function onIntegrityCheck($event)
    {
        $integrityController = $event->sender;

        $integrityController->showTestHeadline("Content Module - Wall Entries " . models\WallEntry::find()->count() . " entries)");
        foreach (models\WallEntry::find()->joinWith('content')->each() as $w) {
            if ($w->content === null) {
                if ($integrityController->showFix("Deleting wall entry id " . $w->id . " without assigned wall entry!")) {
                    $w->delete();
                }
            }
        }

        $integrityController->showTestHeadline("Content Objects (" . Content::find()->count() . " entries)");
        foreach (Content::find()->all() as $content) {
            if ($content->user == null) {
                if ($integrityController->showFix("Deleting content id " . $content->id . " of type " . $content->object_model . " without valid user!")) {
                    $content->delete();
                }
            }
            if ($content->getPolymorphicRelation() == null) {
                if ($integrityController->showFix("Deleting content id " . $content->id . " of type " . $content->object_model . " without valid content object!")) {
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
        $stackWidget->addWidget(widgets\EditLink::className(), ['content' => $content, 'wallEntryWidget' => $stackWidget->wallEntryWidget]);
        $stackWidget->addWidget(widgets\NotificationSwitchLink::className(), ['content' => $content]);
        $stackWidget->addWidget(widgets\PermaLink::className(), ['content' => $content]);
        $stackWidget->addWidget(widgets\StickLink::className(), ['content' => $content]);
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

    /**
     * Handle cron runs
     * 
     * @param \yii\base\ActionEvent $event
     */
    public static function onCronRun($event)
    {
        MailUpdateSender::processCron($event->sender);
    }

    /**
     * On rebuild of the search index, rebuild all user records
     *
     * @param type $event
     */
    public static function onSearchRebuild($event)
    {
        foreach (Content::find()->all() as $content) {
            $contentObject = $content->getPolymorphicRelation();
            if ($contentObject instanceof \humhub\modules\search\interfaces\Searchable) {
                Yii::$app->search->add($contentObject);
            }
        }
    }

    /**
     * After a components\ContentActiveRecord was saved
     * 
     * @param \yii\base\Event $event
     */
    public static function onContentActiveRecordSave($event)
    {
        if ($event->sender instanceof \humhub\modules\search\interfaces\Searchable) {
            Yii::$app->search->update($event->sender);
        }
    }

    /**
     * After a components\ContentActiveRecord was deleted
     * 
     * @param \yii\base\Event $event
     */
    public static function onContentActiveRecordDelete($event)
    {
        if ($event->sender instanceof \humhub\modules\search\interfaces\Searchable) {
            Yii::$app->search->delete($event->sender);
        }
    }

}
