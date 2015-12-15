<?php

use humhub\widgets\TopMenu;

return [
    'id' => 'directory',
    'class' => \humhub\modules\directory\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        array('class' => TopMenu::className(), 'event' => TopMenu::EVENT_INIT, 'callback' => array(humhub\modules\directory\Module::className(), 'onTopMenuInit')),
    ),
    'urlManagerRules' => [
        'directory/members' => 'directory/directory/members',
        'directory/spaces' => 'directory/directory/spaces',
        'directory/profiles' => 'directory/directory/user-posts',
    ]    
];
?>