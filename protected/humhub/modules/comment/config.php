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
    'events' => array(
        array('class' => User::className(), 'event' => User::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onUserDelete')),
        array('class' => ContentActiveRecord::className(), 'event' => ContentActiveRecord::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onContentDelete')),
        array('class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => array(Events::className(), 'onIntegrityCheck')),
        array('class' => WallEntryLinks::className(), 'event' => WallEntryLinks::EVENT_INIT, 'callback' => array(Events::className(), 'onWallEntryLinksInit')),
        array('class' => WallEntryAddons::className(), 'event' => WallEntryAddons::EVENT_INIT, 'callback' => array(Events::className(), 'onWallEntryAddonInit')),
    ),
];
?>