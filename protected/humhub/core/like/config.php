<?php

use yii\db\ActiveRecord;
use humhub\core\like\Module;
use humhub\core\user\models\User;
use humhub\core\content\widgets\WallEntryLinks;
use humhub\core\content\widgets\WallEntryAddons;
use humhub\commands\IntegrityController;

return [
    'id' => 'like',
    'class' => humhub\core\like\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        #array('class' => User::className(), 'event' => User::EVENT_BEFORE_DELETE, 'callback' => array(Module::className(), 'onUserDelete')),
        #array('class' => ActiveRecord::className(), 'event' => ActiveRecord::EVENT_BEFORE_DELETE, 'callback' => array(Module::className(), 'onContentDelete')),
        #array('class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => array(Module::className(), 'onIntegrityCheck')),
        array('class' => WallEntryLinks::className(), 'event' => WallEntryLinks::EVENT_INIT, 'callback' => array(Module::className(), 'onWallEntryLinksInit')),
    ),
];
?>