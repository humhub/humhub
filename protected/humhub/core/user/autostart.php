<?php

use humhub\core\user\models\User;
use humhub\core\search\engine\Search;
use humhub\core\user\Events;

\Yii::$app->moduleManager->register(array(
    'id' => 'user',
    'class' => \humhub\core\user\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => Search::className(), 'event' => Search::EVENT_ON_REBUILD, 'callback' => array(Events::className(), 'onSearchRebuild')),
    )
));
?>