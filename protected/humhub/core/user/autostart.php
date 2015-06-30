<?php

use humhub\core\user\models\User;
use humhub\core\search\engine\Search;
use humhub\core\user\Events;
use humhub\core\content\components\activerecords\Content;
use humhub\core\content\components\activerecords\ContentAddon;

\Yii::$app->moduleManager->register(array(
    'id' => 'user',
    'class' => \humhub\core\user\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => Search::className(), 'event' => Search::EVENT_ON_REBUILD, 'callback' => array(Events::className(), 'onSearchRebuild')),
        array('class' => Content::className(), 'event' => Content::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onContentDelete')),
        array('class' => ContentAddon::className(), 'event' => ContentAddon::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onContentDelete')),
    )
));
?>