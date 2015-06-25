<?php

use \humhub\core\user\models\User;

\Yii::$app->moduleManager->register(array(
    'id' => 'user',
    'class' => \humhub\core\user\Module::className(),
    'isCoreModule' => true,
    'events' => array(
    //array('class' => User::className(), 'event' => User::EVENT_INIT, 'callback' => array('app\modules\user\Events', 'onLoad')),
    )
));
?>