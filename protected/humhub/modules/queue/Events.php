<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\queue;

use Expection;
use Yii;
use yii\base\BaseObject;
use yii\base\Event;
use yii\queue\ExecEvent;
use yii\queue\PushEvent;
use humhub\modules\queue\jobs\CleanupExclusiveJobs;
use humhub\modules\queue\interfaces\ExclusiveJobInterface;
use humhub\modules\queue\helpers\QueueHelper;
use yii\queue\RetryableJobInterface;

/**
 * Events provides callbacks to handle events.
 *
 * @since 1.3
 * @author luke
 */
class Events extends BaseObject
{
    /**
     * Cron call back
     *
     * @param Event $event
     */
    public static function onCronRun(Event $event)
    {
        //Yii::$app->queue->push(new CleanupExclusiveJobs());
    }

    /**
     * Callback for errors while queue execution
     *
     * @param ExecEvent $event
     */
    public static function onQueueError(ExecEvent $event)
    {
        /* @var $exception Expection */
        $exception = $event->error;
        Yii::error(
            'Could not execute queued job! Message: ' . $exception->getMessage(
            ) . ' Trace:' . $exception->getTraceAsString(),
            'queue',
        );
    }

    /**
     * Callback before new jobs in the queue.
     * Handles exclusive jobs.
     *
     * @param PushEvent $event
     */
    public static function onQueueBeforePush(PushEvent $event)
    {
        if ($event->job instanceof ExclusiveJobInterface) {
            // Do not add exclusive jobs if already exists in queue
            if (QueueHelper::isQueued($event->job)) {
                $event->handled = true;
            }
        }

        if ($event->job instanceof LongRunningActiveJob) {
            $event->ttr = Yii::$app->getModule('queue')->longRunningJobTtr;
            ;
        }
    }

    /**
     * Callback after new jobs in the queue.
     * Handles exclusive jobs.
     *
     * @param PushEvent $event
     */
    public static function onQueueAfterPush(PushEvent $event)
    {
        if ($event->job instanceof ExclusiveJobInterface) {
            QueueHelper::markAsQueued($event->id, $event->job);
        }
    }

    public static function onQueueBeforeExec(ExecEvent $event)
    {
        if (!($event->job instanceof RetryableJobInterface) && $event->attempt > 1) {
            Yii::error(
                'Job ' . get_class($event->job) . ' (' . $event->id . ') has failed (Timeout? Max. Execution Time too low?) and is still in queue. Skipping.',
                'queue',
            );
            $event->handled = true;
        }
    }

}
