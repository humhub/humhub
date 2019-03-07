<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\admin\widgets\AuthenticationMenu;
use humhub\modules\ldap\Events;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'ldap',
    'class' => \humhub\modules\ldap\Module::class,
    'isCoreModule' => true,
    'events' => [
        [AuthenticationMenu::class, AuthenticationMenu::EVENT_INIT, [Events::class, 'onAuthenticationMenu']],
    ]
];
?>
