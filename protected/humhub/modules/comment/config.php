<?php

use humhub\modules\comment\Events;
use humhub\modules\user\models\User;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\commands\IntegrityController;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\modules\content\widgets\WallEntryLinks;

return [
    'id' => 'comment',
    'class' => \humhub\modules\comment\Module::className(),
    'isCoreModule' => true,
    'events' => [
        ['class' => User::className(), 'event' => User::EVENT_BEFORE_DELETE, 'callback' => [Events::className(), 'onUserDelete']],
        ['class' => ContentActiveRecord::className(), 'event' => ContentActiveRecord::EVENT_BEFORE_DELETE, 'callback' => [Events::className(), 'onContentDelete']],
        ['class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => [Events::className(), 'onIntegrityCheck']],
        ['class' => WallEntryLinks::className(), 'event' => WallEntryLinks::EVENT_INIT, 'callback' => [Events::className(), 'onWallEntryLinksInit']],
        ['class' => WallEntryAddons::className(), 'event' => WallEntryAddons::EVENT_INIT, 'callback' => [Events::className(), 'onWallEntryAddonInit']],
    ],
];