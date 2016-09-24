<?php

use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\tour\Module;

return [
    'id' => 'tour',
    'class' => Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => Sidebar::className(), 'event' => Sidebar::EVENT_INIT, 'callback' => array(Module::className(), 'onDashboardSidebarInit')),
    ),
];
?>