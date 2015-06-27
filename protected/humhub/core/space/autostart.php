<?php

use humhub\core\search\engine\Search;
use humhub\core\user\models\User;
use humhub\core\space\Events;

Yii::$app->moduleManager->register(array(
    'id' => 'space',
    'class' => \humhub\core\space\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => User::className(), 'event' => User::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onUserDelete')),
        array('class' => Search::className(), 'event' => Search::EVENT_ON_REBUILD, 'callback' => array(Events::className(), 'onSearchRebuild')),
    ),
));
?>