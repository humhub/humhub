<?php

\Yii::$app->moduleManager->register(array(
    'id' => 'post',
    'class' => \humhub\core\post\Module::className(),
    'isCoreModule' => true,
    'events' => array(
    //array('class' => User::className(), 'event' => User::EVENT_INIT, 'callback' => array('app\modules\user\Events', 'onLoad')),
    )
));
?>