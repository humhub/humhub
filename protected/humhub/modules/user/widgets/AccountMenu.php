<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\helpers\ControllerHelper;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\LeftNavigation;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;

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
        $this->panelTitle = Yii::t('UserModule.account', '<strong>Your</strong> Account');

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Profile'),
            'id' => 'account-settings-profile',
            'icon' => 'user',
            'url' => ['/user/account/edit'],
            'sortOrder' => 100,
            'isActive' => ControllerHelper::isActivePath('user', 'account', ['edit', 'change-username', 'change-email', 'change-password', 'delete']),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Notifications'),
            'id' => 'account-settings-notifications',
            'icon' => 'bell',
            'url' => ['/notification/user'],
            'sortOrder' => 106,
            'isActive' => ControllerHelper::isActivePath('notification'),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'General'),
            'id' => 'account-settings-settings',
            'icon' => 'wrench',
            'url' => ['/user/account/edit-settings'],
            'sortOrder' => 110,
            'isActive' => ControllerHelper::isActivePath('user', 'account', 'edit-settings'),
        ]));

        /** @var Module $module */
        $module = Yii::$app->getModule('user');
        if (!empty($module->settings->get('enableProfilePermissions'))) {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('UserModule.account', 'Permissions'),
                'id' => 'account-settings-permissions',
                'icon' => 'lock',
                'url' => ['/user/account/permissions'],
                'sortOrder' => 115,
                'isActive' => ControllerHelper::isActivePath('user', 'account', 'permissions'),
            ]));
        }

        /* @var User $user */
        $user = Yii::$app->user->getIdentity();
        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Modules'),
            'id' => 'account-settings-modules',
            'icon' => 'rocket',
            'url' => ['/user/account/edit-modules'],
            'sortOrder' => 120,
            'isActive' => ControllerHelper::isActivePath('user', 'account', 'edit-modules'),
            'isVisible' => (count($user->moduleManager->getAvailable()) !== 0),
        ]));

        parent::init();
    }

}
