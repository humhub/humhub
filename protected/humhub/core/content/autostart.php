<?php

use humhub\core\content\Events;
use humhub\commands\IntegrityController;
use humhub\core\content\widgets\WallEntryControls;
use humhub\core\content\widgets\WallEntryAddons;

\Yii::$app->moduleManager->register(array(
    'id' => 'content',
    'class' => \humhub\core\content\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => array(Events::className(), 'onIntegrityCheck')),
        array('class' => WallEntryControls::className(), 'event' => WallEntryControls::EVENT_INIT, 'callback' => array(Events::className(), 'onWallEntryControlsInit')),
        array('class' => WallEntryAddons::className(), 'event' => WallEntryAddons::EVENT_INIT, 'callback' => array(Events::className(), 'onWallEntryAddonInit')),
    ),
));
?>