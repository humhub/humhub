<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\LeftNavigation;

/**
 * AccountMenuWidget as (usally left) navigation on users account options.
 *
 * @package humhub.modules_core.user.widgets
 * @since 0.5
 * @author Luke
 */
class AccountMenu extends LeftNavigation
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->panelTitle = Yii::t('UserModule.account', '<strong>Account</strong> settings');

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Profile'),
            'id' => 'account-settings-profile',
            'icon' => 'user',
            'url' => ['/user/account/edit'],
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('user', 'account', ['edit', 'change-email', 'change-password', 'delete'])
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Notifications'),
            'id' => 'account-settings-notifications',
            'icon' => 'bell',
            'url' => ['/notification/user'],
            'sortOrder' => 106,
            'isActive' => MenuLink::isActiveState('notification')
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Settings'),
            'id' => 'account-settings-settings',
            'icon' => 'wrench',
            'url' => ['/user/account/edit-settings'],
            'sortOrder' => 110,
            'isActive' => MenuLink::isActiveState('user', 'account', 'edit-settings')
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Security'),
            'id' => 'account-settings-security',
            'icon' => 'lock',
            'url' => ['/user/account/security'],
            'sortOrder' => 115,
            'isActive' => MenuLink::isActiveState('user', 'account', 'security')
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Modules'),
            'id' => 'account-settings-modules',
            'icon' => 'rocket',
            'url' => ['/user/account/edit-modules'],
            'sortOrder' => 120,
            'isActive' => MenuLink::isActiveState('user', 'account', 'edit-modules'),
            'isVisible' => (count(Yii::$app->user->getIdentity()->getAvailableModules()) !== 0)
        ]));

        parent::init();
    }

}
