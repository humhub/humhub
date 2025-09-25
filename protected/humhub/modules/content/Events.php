<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content;

use humhub\commands\CronController;
use humhub\commands\IntegrityController;
use humhub\components\Event;
use humhub\modules\content\models\Content;
use humhub\modules\content\services\ContentSearchService;
use humhub\modules\user\events\UserEvent;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Console;

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
            $content->hardDelete();
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
            $content->hardDelete();
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
            /* @var Content $content */
            if ($content->createdBy == null) {
                if ($integrityController->showFix('Deleting content id ' . $content->id . ' of type ' . $content->object_model . ' without valid user!')) {
                    $content->hardDelete();
                }
            }
            if ($content->getPolymorphicRelation() === null) {
                if ($integrityController->showFix('Deleting content id ' . $content->id . ' of type ' . $content->object_model . ' without valid content object!')) {
                    $content->hardDelete();
                }
            }
        }
    }

    /**
     * On init of the WallEntryAddonWidget, attach the wall entry links widget.
     *
     * @param Event $event
     */
    public static function onWallEntryAddonInit($event)
    {
        $event->sender->addWidget(
            widgets\WallEntryLinks::class,
            [
                'object' => $event->sender->object,
            ],
            ['sortOrder' => 10],
        );
    }

    /**
     * After a Content was deleted
     *
     * @param \yii\base\Event $event
     */
    public static function onContentAfterDelete($event)
    {
        /* @var Content $content */
        $content = $event->sender;

        (new ContentSearchService($content))->delete();
    }

    /**
     * Callback on daily cron job run
     */
    public static function onCronDailyRun(): void
    {
        Yii::$app->queue->push(new jobs\PurgeDeletedContents());
    }

    /**
     * Callback on before run cron action
     */
    public static function onCronBeforeAction($event): void
    {
        if (self::canPublishScheduledContent()) {
            /* @var CronController $controller */
            $controller = $event->sender;
            $controller->stdout('Publish scheduled content... ');
            self::publishScheduledContent();
            $controller->stdout('done.' . PHP_EOL, Console::FG_GREEN);
        }
    }

    private static function canPublishScheduledContent(): bool
    {
        $lastPublishTime = self::getModule()->settings->get('lastPublishScheduledTS');
        return $lastPublishTime === null
            || time() >= $lastPublishTime + self::getModule()->publishScheduledInterval * 60;
    }

    private static function publishScheduledContent()
    {
        if (Yii::$app->queue->push(new jobs\PublishScheduledContents())) {
            self::getModule()->settings->set('lastPublishScheduledTS', time());
        }
    }

    private static function getModule(): Module
    {
        return Yii::$app->getModule('content');
    }
}
