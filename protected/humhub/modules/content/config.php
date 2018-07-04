<?php

use humhub\modules\content\Events;
use humhub\commands\IntegrityController;
use humhub\modules\content\widgets\WallEntryControls;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\search\engine\Search;
use humhub\modules\content\components\ContentActiveRecord;

return [
    'id' => 'content',
    'class' => \humhub\modules\content\Module::class,
    'isCoreModule' => true,
    'events' => [
        ['class' => IntegrityController::class, 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => [Events::class, 'onIntegrityCheck']],
        ['class' => WallEntryAddons::class, 'event' => WallEntryAddons::EVENT_INIT, 'callback' => [Events::class, 'onWallEntryAddonInit']],
        ['class' => User::class, 'event' => User::EVENT_BEFORE_DELETE, 'callback' => [Events::class, 'onUserDelete']],
        ['class' => User::class, 'event' => User::EVENT_BEFORE_SOFT_DELETE, 'callback' => [Events::class, 'onUserSoftDelete']],
        ['class' => Space::class, 'event' => User::EVENT_BEFORE_DELETE, 'callback' => [Events::class, 'onSpaceDelete']],
        ['class' => Search::class, 'event' => Search::EVENT_ON_REBUILD, 'callback' => [Events::class, 'onSearchRebuild']],
        ['class' => ContentActiveRecord::class, 'event' => ContentActiveRecord::EVENT_AFTER_INSERT, 'callback' => [Events::class, 'onContentActiveRecordSave']],
        ['class' => ContentActiveRecord::class, 'event' => ContentActiveRecord::EVENT_AFTER_UPDATE, 'callback' => [Events::class, 'onContentActiveRecordSave']],
        ['class' => ContentActiveRecord::class, 'event' => ContentActiveRecord::EVENT_AFTER_DELETE, 'callback' => [Events::class, 'onContentActiveRecordDelete']],
    ],
];
?>