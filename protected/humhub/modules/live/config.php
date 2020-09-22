<?php

use humhub\modules\live\Events;
use humhub\modules\space\models\Membership;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\user\models\Follow;
use humhub\commands\CronController;

return [
    'id' => 'live',
    'class' => humhub\modules\live\Module::class,
    'isCoreModule' => true,
    'events' => [
        [Membership::class, Membership::EVENT_MEMBER_ADDED, [Events::class, 'onMemberEvent']],
        [Membership::class, Membership::EVENT_MEMBER_REMOVED, [Events::class, 'onMemberEvent']],
        [Friendship::class, Friendship::EVENT_FRIENDSHIP_CREATED, [Events::class, 'onFriendshipEvent']],
        [Friendship::class, Friendship::EVENT_FRIENDSHIP_REMOVED, [Events::class, 'onFriendshipEvent']],
        [Follow::class, Follow::EVENT_FOLLOWING_CREATED, [Events::class, 'onFollowEvent']],
        [Follow::class, Follow::EVENT_FOLLOWING_REMOVED, [Events::class, 'onFollowEvent']],
        [CronController::class, CronController::EVENT_ON_HOURLY_RUN, [Events::class, 'onHourlyCronRun']]
    ],
];
?>