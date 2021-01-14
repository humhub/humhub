<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content;

use humhub\commands\IntegrityController;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\search\interfaces\Searchable;
use humhub\modules\search\libs\SearchHelper;
use humhub\modules\user\events\UserEvent;
use Yii;
use yii\base\BaseObject;

/**
 * Events provides callbacks to handle events.
 *
 * @author luke
 */
class Events extends BaseObject
{

    /**
     * Callback when a user is soft deleted.
     *
     * @param UserEvent $event
     */
    public static function onUserSoftDelete(UserEvent $event)
    {
        // Delete user profile content on soft delete
        foreach (Content::findAll(['contentcontainer_id' => $event->user->contentcontainer_id]) as $content) {
            $content->delete();
        }
    }

    /**
     * Callback when a user is completely deleted.
     *
     * @param \yii\base\Event $event
     */
    public static function onUserDelete($event)
    {
        $user = $event->sender;
        foreach (Content::findAll(['created_by' => $user->id]) as $content) {
            $content->delete();
        }
    }

    /**
     * Callback when a user is completely deleted.
     *
     * @param \yii\base\Event $event
     */
    public static function onSpaceDelete($event)
    {
        $space = $event->sender;
        foreach (Content::findAll(['contentcontainer_id' => $space->contentContainerRecord->id]) as $content) {
            $content->delete();
        }
    }

    /**
     * Callback to validate module database records.
     *
     * @param Event $event
     */
    public static function onIntegrityCheck($event)
    {
        /** @var IntegrityController $integrityController */
        $integrityController = $event->sender;

        $integrityController->showTestHeadline('Content Objects (' . Content::find()->count() . ' entries)');
        foreach (Content::find()->each() as $content) {
            if ($content->createdBy == null) {
                if ($integrityController->showFix('Deleting content id ' . $content->id . ' of type ' . $content->object_model . ' without valid user!')) {
                    $content->delete();
                }
            }
            if ($content->getPolymorphicRelation() === null) {
                if ($integrityController->showFix('Deleting content id ' . $content->id . ' of type ' . $content->object_model . ' without valid content object!')) {
                    $content->delete();
                }
            }
        }
    }

    /**
     * On init of the WallEntryAddonWidget, attach the wall entry links widget.
     *
     * @param CEvent $event
     */
    public static function onWallEntryAddonInit($event)
    {
        $event->sender->addWidget(widgets\WallEntryLinks::class, [
            'object' => $event->sender->object,
        ], ['sortOrder' => 10]
        );
    }

    /**
     * On rebuild of the search index, rebuild all user records
     *
     * @param type $event
     */
    public static function onSearchRebuild($event)
    {
        foreach (Content::find()->each() as $content) {
            $contentObject = $content->getPolymorphicRelation();
            if ($contentObject instanceof Searchable) {
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
        /** @var ContentActiveRecord $record */
        $record = $event->sender;
        SearchHelper::queueUpdate($record);
    }

    /**
     * After a components\ContentActiveRecord was deleted
     *
     * @param \yii\base\Event $event
     */
    public static function onContentActiveRecordDelete($event)
    {
        /** @var ContentActiveRecord $record */
        $record = $event->sender;
        SearchHelper::queueDelete($record);
    }

}
