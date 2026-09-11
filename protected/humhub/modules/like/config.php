<?php

use humhub\commands\IntegrityController;
use humhub\components\api\ApiRules;
use humhub\models\RecordMap;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\user\models\User;
use humhub\modules\content\widgets\WallEntryLinks;

return [
    'id' => 'like',
    'class' => humhub\modules\like\Module::class,
    'isCoreModule' => true,
    // HTTP API (see docs/develop/concept-api.md) — records are addressed by `recordId` or
    // `model`+`pk` in the query string, so the patterns carry no id segment.
    'urlManagerRules' => ApiRules::v2([
        ['pattern' => 'like/state', 'route' => 'like/api/like/state', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'like/states', 'route' => 'like/api/like/states', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'like/users', 'route' => 'like/api/like/users', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'like', 'route' => 'like/api/like/create', 'verb' => 'POST'],
        ['pattern' => 'like', 'route' => 'like/api/like/remove', 'verb' => 'DELETE'],
    ]),
    'events' => [
        ['class' => User::class, 'event' => User::EVENT_BEFORE_DELETE, 'callback' => ['humhub\modules\like\Events', 'onUserDelete']],
        ['class' => RecordMap::class, 'event' => RecordMap::EVENT_BEFORE_DELETE, 'callback' => ['humhub\modules\like\Events', 'onRecordMapDelete']],
        ['class' => ContentActiveRecord::class, 'event' => ContentActiveRecord::EVENT_BEFORE_DELETE, 'callback' => ['humhub\modules\like\Events', 'onContentDelete']],
        ['class' => Content::class, 'event' => Content::EVENT_BEFORE_HARD_DELETE, 'callback' => ['humhub\modules\like\Events', 'onContentHardDelete']],
        ['class' => IntegrityController::class, 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => ['humhub\modules\like\Events', 'onIntegrityCheck']],
        ['class' => WallEntryLinks::class, 'event' => WallEntryLinks::EVENT_INIT, 'callback' => ['humhub\modules\like\Events', 'onWallEntryLinksInit']],
    ],
];
