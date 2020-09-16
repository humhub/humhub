<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\admin\widgets\AuthenticationMenu;
use humhub\modules\ldap\Events;
use humhub\modules\user\authclient\Collection;
use humhub\components\console\Application;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'ldap',
    'class' => \humhub\modules\ldap\Module::class,
    'isCoreModule' => true,
    'consoleControllerMap' => [
        'ldap' => 'humhub\modules\ldap\commands\LdapController'
    ],
    'events' => [
        [AuthenticationMenu::class, AuthenticationMenu::EVENT_INIT, [Events::class, 'onAuthenticationMenu']],
        [Collection::class, Collection::EVENT_BEFORE_CLIENTS_SET, [Events::class, 'onAuthClientCollectionSet']],
    ]
];
?>
