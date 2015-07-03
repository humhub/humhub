<?php

use humhub\widgets\TopMenuRightStack;
use humhub\core\search\Events;
use humhub\components\console\Application;
use humhub\commands\CronController;

return [
    'isCoreModule' => true,
    'id' => 'search',
    'class' => \humhub\core\search\Module::className(),
    'events' => array(
        ['class' => TopMenuRightStack::className(), 'event' => TopMenuRightStack::EVENT_INIT, 'callback' => array(Events::className(), 'onTopMenuRightInit')],
        ['class' => Application::className(), 'event' => Application::EVENT_ON_INIT, 'callback' => array(Events::className(), 'onConsoleApplicationInit')],
        ['class' => CronController::className(), 'event' => CronController::EVENT_ON_HOURLY_RUN, 'callback' => [Events::className(), 'onHourlyCron']],
    //array('class' => 'Comment', 'event' => 'onAfterSave', 'callback' => array('SearchModuleEvents', 'onAfterSaveComment')),
    ),
];
?>