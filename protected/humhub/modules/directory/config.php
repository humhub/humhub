<?php

use humhub\modules\directory\Module;
use humhub\widgets\TopMenu;

return [
    'id' => 'directory',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        ['class' => TopMenu::class, 'event' => TopMenu::EVENT_INIT, 'callback' => [Module::class, 'onTopMenuInit']],
    ],
    'urlManagerRules' => [
        'directory/members' => 'directory/directory/members',
        'directory/spaces' => 'directory/directory/spaces',
        'directory/profiles' => 'directory/directory/user-posts',
    ]    
];
?>