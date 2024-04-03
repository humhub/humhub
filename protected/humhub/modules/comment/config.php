<?php

use humhub\modules\comment\Events;
use humhub\modules\comment\Module;
use humhub\modules\user\models\User;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\commands\IntegrityController;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\modules\content\widgets\WallEntryLinks;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'comment',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        [User::class, User::EVENT_BEFORE_DELETE, [Events::class, 'onUserDelete']],
        [ContentActiveRecord::class, ContentActiveRecord::EVENT_BEFORE_DELETE, [Events::class, 'onContentDelete']],
        [IntegrityController::class, IntegrityController::EVENT_ON_RUN, [Events::class, 'onIntegrityCheck']],
        [WallEntryLinks::class, WallEntryLinks::EVENT_INIT, [Events::class, 'onWallEntryLinksInit']],
        [WallEntryAddons::class, WallEntryAddons::EVENT_INIT, [Events::class, 'onWallEntryAddonInit']],
    ],
];
