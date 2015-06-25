<?php

\Yii::$app->moduleManager->register(array(
    'id' => 'dashboard',
    'class' => \humhub\core\dashboard\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => \humhub\widgets\TopMenu::className(), 'event' => \humhub\widgets\TopMenu::EVENT_INIT, 'callback' => array('\humhub\core\dashboard\Events', 'onTopMenuInit')),
    ),
));
?>