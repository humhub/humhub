<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\helpers\ControllerHelper;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\TabMenu;
use Yii;

/**
 * Account Settings Tab Menu
 */
class AccountProfileMenu extends TabMenu
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.base', 'Profile'),
            'url' => ['/user/account/edit'],
            'sortOrder' => 100,
            'isActive' => ControllerHelper::isActivePath('user', 'account', 'edit'),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.base', 'Change Username'),
            'url' => ['/user/account/change-username'],
            'sortOrder' => 200,
            'isActive' => ControllerHelper::isActivePath('user', 'account', 'change-username'),
            'isVisible' => Yii::$app->user->getAuthClientUserService()->canChangeUsername(),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.base', 'Change Email'),
            'url' => ['/user/account/change-email'],
            'sortOrder' => 200,
            'isActive' => ControllerHelper::isActivePath('user', 'account', ['change-email', 'change-email-validate']),
            'isVisible' => Yii::$app->user->getAuthClientUserService()->canChangeEmail(),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.base', 'Change Password'),
            'url' => ['/user/account/change-password'],
            'sortOrder' => 400,
            'isActive' => ControllerHelper::isActivePath('user', 'account', 'change-password'),
            'isVisible' => Yii::$app->user->getAuthClientUserService()->canChangePassword(),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.base', 'Delete Account'),
            'url' => ['/user/account/delete'],
            'sortOrder' => 500,
            'isActive' => ControllerHelper::isActivePath('user', 'account', 'delete'),
            'isVisible' => Yii::$app->user->getAuthClientUserService()->canDeleteAccount(),
        ]));

        parent::init();
    }
}
