<?php

use humhub\widgets\TopMenu;

return [
    'id' => 'directory',
    'class' => \humhub\modules\directory\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => TopMenu::className(), 'event' => TopMenu::EVENT_INIT, 'callback' => array(humhub\modules\directory\Module::className(), 'onTopMenuInit')),
    ),
];
?>