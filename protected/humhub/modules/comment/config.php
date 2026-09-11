<?php

use humhub\components\api\ApiRules;
use humhub\modules\comment\Events;
use humhub\modules\comment\Module;
use humhub\modules\user\models\User;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use humhub\commands\IntegrityController;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\modules\content\widgets\WallEntryLinks;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'comment',
    'class' => Module::class,
    'isCoreModule' => true,
    // HTTP API (see docs/develop/concept-api.md). Routes point at
    // `controllers/api/CommentController`; Yii resolves the `api/` subdirectory from the
    // route itself. Registered prepended by the ModuleManager, so they win over the
    // generic fallback routing.
    'urlManagerRules' => ApiRules::v2([
        ['pattern' => 'comment/content/<id:\d+>/window', 'route' => 'comment/api/comment/window-by-content', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'comment/parent/<id:\d+>/window', 'route' => 'comment/api/comment/window-by-parent', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'comment', 'route' => 'comment/api/comment/create', 'verb' => 'POST'],
        ['pattern' => 'comment/<id:\d+>', 'route' => 'comment/api/comment/view', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'comment/<id:\d+>/permissions', 'route' => 'comment/api/comment/permissions', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'comment/<id:\d+>', 'route' => 'comment/api/comment/update', 'verb' => ['PUT', 'PATCH']],
        ['pattern' => 'comment/<id:\d+>', 'route' => 'comment/api/comment/delete', 'verb' => 'DELETE'],
    ]),
    'events' => [
        [User::class, User::EVENT_BEFORE_DELETE, [Events::class, 'onUserDelete']],
        [ContentActiveRecord::class, ContentActiveRecord::EVENT_BEFORE_DELETE, [Events::class, 'onContentDelete']],
        [Content::class, Content::EVENT_BEFORE_HARD_DELETE, [Events::class, 'onContentHardDelete']],
        [IntegrityController::class, IntegrityController::EVENT_ON_RUN, [Events::class, 'onIntegrityCheck']],
        [WallEntryLinks::class, WallEntryLinks::EVENT_INIT, [Events::class, 'onWallEntryLinksInit']],
        [WallEntryAddons::class, WallEntryAddons::EVENT_INIT, [Events::class, 'onWallEntryAddonInit']],
    ],
];
