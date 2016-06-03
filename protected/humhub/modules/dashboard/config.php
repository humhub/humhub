<?php

return [
    'id' => 'dashboard',
    'class' => \humhub\modules\dashboard\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => \humhub\widgets\TopMenu::className(), 'event' => \humhub\widgets\TopMenu::EVENT_INIT, 'callback' => array('\humhub\modules\dashboard\Events', 'onTopMenuInit')),
    ),
    'urlManagerRules' => [
        'dashboard' => 'dashboard/dashboard'
    ]
];
?>