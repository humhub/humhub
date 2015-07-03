<?php

use humhub\modules\user\models\User;
use humhub\modules\search\engine\Search;
use humhub\modules\user\Events;
use humhub\modules\content\components\activerecords\Content;
use humhub\modules\content\components\activerecords\ContentAddon;

return [
    'id' => 'user',
    'class' => \humhub\modules\user\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => Search::className(), 'event' => Search::EVENT_ON_REBUILD, 'callback' => array(Events::className(), 'onSearchRebuild')),
        array('class' => Content::className(), 'event' => Content::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onContentDelete')),
        array('class' => ContentAddon::className(), 'event' => ContentAddon::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onContentDelete')),
    )
];
?>