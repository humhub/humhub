<?php

use humhub\modules\comment\Events;
use humhub\modules\user\models\User;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\commands\IntegrityController;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\modules\content\widgets\WallEntryLinks;
use humhub\modules\search\engine\Search;

return [
    'id' => 'comment',
    'class' => \humhub\modules\comment\Module::class,
    'isCoreModule' => true,
    'events' => [
        ['class' => User::class, 'event' => User::EVENT_BEFORE_DELETE, 'callback' => [Events::class, 'onUserDelete']],
        ['class' => ContentActiveRecord::class, 'event' => ContentActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::class, 'onContentDelete']],
        ['class' => IntegrityController::class, 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => [Events::class, 'onIntegrityCheck']],
        ['class' => WallEntryLinks::class, 'event' => WallEntryLinks::EVENT_INIT, 'callback' => [Events::class, 'onWallEntryLinksInit']],
        ['class' => WallEntryAddons::class, 'event' => WallEntryAddons::EVENT_INIT, 'callback' => [Events::class, 'onWallEntryAddonInit']],
        ['class' => Search::class, 'event' => Search::EVENT_SEARCH_ATTRIBUTES, 'callback' => [Events::class, 'onSearchAttributes']]
    ],
];
