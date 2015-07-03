<?php

use humhub\core\comment\Events;
use humhub\core\user\models\User;
use humhub\core\content\components\activerecords\Content;
use humhub\commands\IntegrityController;
use humhub\core\content\widgets\WallEntryAddons;
use humhub\core\content\widgets\WallEntryLinks;

return [
    'id' => 'comment',
    'class' => \humhub\core\comment\Module::className(),
    'isCoreModule' => true,
    // Events to Catch
    'events' => array(
        array('class' => User::className(), 'event' => User::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onUserDelete')),
        array('class' => Content::className(), 'event' => Content::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onContentDelete')),
        array('class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => array(Events::className(), 'onIntegrityCheck')),
        array('class' => WallEntryLinks::className(), 'event' => WallEntryLinks::EVENT_INIT, 'callback' => array(Events::className(), 'onWallEntryLinksInit')),
        array('class' => WallEntryAddons::className(), 'event' => WallEntryAddons::EVENT_INIT, 'callback' => array(Events::className(), 'onWallEntryAddonInit')),
    ),
];
?>