<?php

use humhub\core\search\engine\Search;
use humhub\core\user\models\User;
use humhub\core\space\Events;
use humhub\components\console\Application;

Yii::$app->moduleManager->register(array(
    'id' => 'space',
    'class' => \humhub\core\space\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => User::className(), 'event' => User::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onUserDelete')),
        array('class' => Search::className(), 'event' => Search::EVENT_ON_REBUILD, 'callback' => array(Events::className(), 'onSearchRebuild')),
        array('class' => Application::className(), 'event' => Application::EVENT_ON_INIT, 'callback' => array(Events::className(), 'onConsoleApplicationInit')),        
    ),
));
?>