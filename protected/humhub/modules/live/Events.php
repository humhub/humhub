<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live;

use Yii;
use humhub\modules\live\Module;
use humhub\modules\friendship\FriendshipEvent;
use humhub\modules\space\MemberEvent;
use humhub\modules\user\events\FollowEvent;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * Events provides callbacks to handle events.
 * 
 * @since 1.2
 * @author luke
 */
class Events extends \yii\base\Object
{

    /**
     * On hourly cron job, add database cleanup task
     */
    public static function onHourlyCronRun()
    {
        Yii::$app->queue->push(new jobs\DatabaseCleanup());
    }

    /**
     * MemberEvent is called when a user left or joined a space
     * Used to clear the cache legitimate cache.
     */
    public static function onMemberEvent(MemberEvent $event)
    {
        Yii::$app->cache->delete(Module::$legitimateCachePrefix . $event->user->id);
    }

    /**
     * FriendshipEvent is called when a friendship was created or removed
     * Used to clear the cache legitimate cache.
     */
    public static function onFriendshipEvent(FriendshipEvent $event)
    {
        Yii::$app->cache->delete(Module::$legitimateCachePrefix . $event->user1->id);
        Yii::$app->cache->delete(Module::$legitimateCachePrefix . $event->user2->id);
    }

    /**
     * FollowEvent is called when a following was created or removed
     * Used to clear the cache legitimate cache.
     */
    public static function onFollowEvent(FollowEvent $event)
    {
        if ($event->target instanceof ContentContainerActiveRecord) {
            Yii::$app->cache->delete(Module::$legitimateCachePrefix . $event->user->id);
        }
    }

}
