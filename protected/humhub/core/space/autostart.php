<?php

Yii::$app->moduleManager->register(array(
    'id' => 'space',
    'class' => \humhub\core\space\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => humhub\core\user\models\User::className(), 'event' => 'onBeforeDelete', 'callback' => array('SpaceModule', 'onUserDelete')),
    ),
));
?>