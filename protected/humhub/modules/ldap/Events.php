<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ldap;

use humhub\components\Event;
use humhub\helpers\ControllerHelper;
use humhub\modules\admin\widgets\AuthenticationMenu;
use humhub\modules\ldap\models\LdapSettings;
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

    /**
     * @param $event Event
     */
    public static function onAuthClientCollectionSet($event)
    {
        if (LdapSettings::isEnabled()) {
            /** @var Collection $collection */
            $collection = $event->sender;

            $settings = new LdapSettings();
            $settings->loadSaved();

            $configParams = $event->parameters['clients']['ldap'] ?? [];
            $collection->setClient('ldap', array_merge($settings->getLdapAuthDefinition(), $configParams));
        }
    }
}
