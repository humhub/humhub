<?php

use humhub\modules\friendship\Events;
use humhub\modules\user\widgets\AccountMenu;

return [
    'id' => 'friendship',
    'class' => \humhub\modules\friendship\Module::className(),
    'isCoreModule' => true,
    'events' => [
        array('class' => AccountMenu::className(), 'event' => AccountMenu::EVENT_INIT, 'callback' => array(Events::className(), 'onAccountMenuInit')),
    ]
];
?>