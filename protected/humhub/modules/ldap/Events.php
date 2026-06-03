<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap;

use humhub\components\Event;
use humhub\helpers\ControllerHelper;
use humhub\libs\ParameterEvent;
use humhub\modules\admin\widgets\AuthenticationMenu;
use humhub\modules\ldap\authclient\LdapAuth;
use humhub\modules\ldap\jobs\LdapSyncJob;
use humhub\modules\ldap\models\LdapSettings;
use humhub\modules\ldap\source\LdapUserSource;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\user\authclient\Collection;
use Yii;
use yii\base\BaseObject;

/**
 * Events provides callbacks for all defined module events.
 *
 * @author luke
 */
class Events extends BaseObject
{
    /**
     * @param $event Event
     */
    public static function onAuthenticationMenu($event)
    {
        /* @var AuthenticationMenu $menu */
        $menu = $event->sender;

        $menu->addEntry(new MenuLink([
            'label' => Yii::t('LdapModule.base', 'LDAP'),
            'url' => ['/ldap/admin'],
            'sortOrder' => 200,
            'isActive' => ControllerHelper::isActivePath('ldap', 'admin'),
        ]));
    }

    public static function onHourlyCron(): void
    {
        if (LdapSettings::isEnabled()) {
            Yii::$app->queue->push(new LdapSyncJob());
        }
    }

    /**
     * Registers an LdapAuth client for every connection in the registry
     * (default 'ldap' from DB UI plus any extra connections from config).
     *
     * @param $event Event
     */
    public static function onAuthClientCollectionSet($event)
    {
        /** @var Collection $collection */
        $collection = $event->sender;

        foreach (self::getRegistryConnectionIds() as $connectionId) {
            $configOverrides = $event->parameters['clients'][$connectionId] ?? [];
            $collection->setClient($connectionId, array_merge([
                'class' => LdapAuth::class,
                'connectionId' => $connectionId,
                'clientId' => $connectionId,
            ], $configOverrides));
        }
    }

    /**
     * Registers an LdapUserSource for every connection in the registry.
     *
     * @param $event ParameterEvent
     */
    public static function onUserSourceCollectionSet($event)
    {
        $settings = null;

        foreach (self::getRegistryConnectionIds() as $connectionId) {
            $sourceConfig = [
                'class' => LdapUserSource::class,
                'connectionId' => $connectionId,
                'allowedAuthClientIds' => [$connectionId],
            ];

            // The legacy 'ldap' connection inherits the auth-client allow list
            // from LdapSettings (admin UI). Extra connections from config use
            // their own auth client only.
            if ($connectionId === 'ldap') {
                $settings ??= (new LdapSettings());
                $settings->loadSaved();
                $sourceConfig['allowedAuthClientIds'] = $settings->allowedAuthClientIds;
            }

            $event->parameters['userSources'][$connectionId] = $sourceConfig;
        }
    }

    /**
     * @return string[] connection IDs currently registered in the LDAP module's registry
     */
    private static function getRegistryConnectionIds(): array
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('ldap');
        return $module->getConnectionRegistry()->getIds();
    }
}
