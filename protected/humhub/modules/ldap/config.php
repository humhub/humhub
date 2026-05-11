<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\commands\CronController;
use humhub\modules\admin\widgets\AuthenticationMenu;
use humhub\modules\ldap\Events;
use humhub\modules\ldap\Module;
use humhub\modules\user\authclient\Collection;
use humhub\modules\user\source\UserSourceCollection;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'ldap',
    'class' => Module::class,
    'isCoreModule' => true,
    'consoleControllerMap' => [
        'ldap' => 'humhub\modules\ldap\commands\LdapController',
    ],
    'events' => [
        [AuthenticationMenu::class, AuthenticationMenu::EVENT_INIT, [Events::class, 'onAuthenticationMenu']],
        [Collection::class, Collection::EVENT_BEFORE_CLIENTS_SET, [Events::class, 'onAuthClientCollectionSet']],
        [UserSourceCollection::class, UserSourceCollection::EVENT_BEFORE_USER_SOURCES_SET, [Events::class, 'onUserSourceCollectionSet']],
        [CronController::class, CronController::EVENT_ON_HOURLY_RUN, [Events::class, 'onHourlyCron']],
    ],
];
