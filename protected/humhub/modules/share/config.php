<?php

use humhub\modules\dashboard\widgets\Sidebar;

return [
    'id' => 'share',
    'class' => 'humhub\modules\share\Module',
    'namespace' => 'humhub\modules\share',
    'isCoreModule' => true,
    'events' => array(
        array('class' => Sidebar::className(), 'event' => Sidebar::EVENT_INIT, 'callback' => array('humhub\modules\share\Module', 'onSidebarInit')),
    ),
];
?>
