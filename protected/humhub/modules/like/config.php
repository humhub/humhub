<?php

use humhub\components\ActiveRecord;
use humhub\commands\IntegrityController;
use humhub\modules\like\Module;
use humhub\modules\user\models\User;
use humhub\modules\content\widgets\WallEntryLinks;

return [
    'id' => 'like',
    'class' => humhub\modules\like\Module::className(),
    'isCoreModule' => true,
    'events' => [
        ['class' => User::className(), 'event' => User::EVENT_BEFORE_DELETE, 'callback' => ['humhub\modules\like\Events', 'onUserDelete']],
        ['class' => ActiveRecord::className(), 'event' => ActiveRecord::EVENT_BEFORE_DELETE, 'callback' => ['humhub\modules\like\Events', 'onActiveRecordDelete']],
        ['class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => ['humhub\modules\like\Events', 'onIntegrityCheck']],
        ['class' => WallEntryLinks::className(), 'event' => WallEntryLinks::EVENT_INIT, 'callback' => ['humhub\modules\like\Events', 'onWallEntryLinksInit']],
    ],
];
