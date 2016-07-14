<?php

use humhub\modules\dashboard\widgets\Sidebar;

return [
    'id' => 'dashboard',
    'class' => \humhub\modules\dashboard\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => \humhub\widgets\TopMenu::className(), 'event' => \humhub\widgets\TopMenu::EVENT_INIT, 'callback' => array('\humhub\modules\dashboard\Events', 'onTopMenuInit')),
        array('class' => Sidebar::className(), 'event' => Sidebar::EVENT_INIT, 'callback' => array('\humhub\modules\dashboard\Events', 'onSidebarInit')),
    ),
    'urlManagerRules' => [
        'dashboard' => 'dashboard/dashboard'
    ]
];
?>