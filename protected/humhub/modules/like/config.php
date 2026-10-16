<?php

use humhub\commands\IntegrityController;
use humhub\models\RecordMap;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\user\models\User;
use humhub\modules\content\widgets\WallEntryLinks;

return [
    'id' => 'like',
    'class' => humhub\modules\like\Module::class,
    'isCoreModule' => true,
    // HTTP API (see docs/develop/concept-api.md).
    'urlManagerRules' => [
        // The caller's like of one record: the record id in the path, set with PUT and removed
        // with DELETE - the same shape as space/<id>/membership and user/<id>/friendship.
        ['pattern' => 'api/v2/like/<recordId:\d+>', 'route' => 'like/api/like/state', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/like/<recordId:\d+>', 'route' => 'like/api/like/affirm', 'verb' => 'PUT'],
        ['pattern' => 'api/v2/like/<recordId:\d+>', 'route' => 'like/api/like/remove', 'verb' => 'DELETE'],
        ['pattern' => 'api/v2/like/<recordId:\d+>/users', 'route' => 'like/api/like/users', 'verb' => ['GET', 'HEAD']],
        // The states of many records at once, for a window of them.
        ['pattern' => 'api/v2/like/states', 'route' => 'like/api/like/states', 'verb' => ['GET', 'HEAD']],
    ],
    'events' => [
        ['class' => User::class, 'event' => User::EVENT_BEFORE_DELETE, 'callback' => ['humhub\modules\like\Events', 'onUserDelete']],
        ['class' => RecordMap::class, 'event' => RecordMap::EVENT_BEFORE_DELETE, 'callback' => ['humhub\modules\like\Events', 'onRecordMapDelete']],
        ['class' => ContentActiveRecord::class, 'event' => ContentActiveRecord::EVENT_BEFORE_DELETE, 'callback' => ['humhub\modules\like\Events', 'onContentDelete']],
        ['class' => Content::class, 'event' => Content::EVENT_BEFORE_HARD_DELETE, 'callback' => ['humhub\modules\like\Events', 'onContentHardDelete']],
        ['class' => IntegrityController::class, 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => ['humhub\modules\like\Events', 'onIntegrityCheck']],
        ['class' => WallEntryLinks::class, 'event' => WallEntryLinks::EVENT_INIT, 'callback' => ['humhub\modules\like\Events', 'onWallEntryLinksInit']],
    ],
];
