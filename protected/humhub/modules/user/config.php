<?php

use humhub\modules\search\engine\Search;
use humhub\modules\user\Events;
use humhub\commands\IntegrityController;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\components\ContentActiveRecord;

return [
    'id' => 'user',
    'class' => \humhub\modules\user\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => Search::className(), 'event' => Search::EVENT_ON_REBUILD, 'callback' => array(Events::className(), 'onSearchRebuild')),
        array('class' => ContentActiveRecord::className(), 'event' => ContentActiveRecord::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onContentDelete')),
        array('class' => ContentAddonActiveRecord::className(), 'event' => ContentAddonActiveRecord::EVENT_BEFORE_DELETE, 'callback' => array(Events::className(), 'onContentDelete')),
        array('class' => IntegrityController::className(), 'event' => IntegrityController::EVENT_ON_RUN, 'callback' => array(Events::className(), 'onIntegrityCheck')),
    )
];
?>