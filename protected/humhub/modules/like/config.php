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
    'events' => array(
        array('class' => User::className(), 'event' => User::EVENT_BEFORE_DELETE, 'callback' => array(Module::className(), 'onUserDelete')),
        array('class' => ActiveRecord::className(), 'event' => ActiveRecord::EVENT_BEFORE_DELETE, 'callback' => array(Module::className(), 'onActiveRecordDelete')),
        array('class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => array(Module::className(), 'onIntegrityCheck')),
        array('class' => WallEntryLinks::className(), 'event' => WallEntryLinks::EVENT_INIT, 'callback' => array(Module::className(), 'onWallEntryLinksInit')),
    ),
];
?>