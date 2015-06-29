<?php

use humhub\widgets\TopMenu;

Yii::$app->moduleManager->register(array(
    'id' => 'directory',
    'class' => \humhub\core\directory\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => TopMenu::className(), 'event' => TopMenu::EVENT_INIT, 'callback' => array(humhub\core\directory\Module::className(), 'onTopMenuInit')),
    ),
));
?>