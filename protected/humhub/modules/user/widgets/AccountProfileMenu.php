<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\TabMenu;

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
            'label' => Yii::t('UserModule.base', 'General'),
            'url' => ['/user/account/edit'],
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('user', 'account', 'edit')
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.base', 'Change Username'),
            'url' => ['/user/account/change-username'],
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState('user', 'account', 'change-username'),
            'isVisible' => Yii::$app->user->canChangeUsername()
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.base', 'Change Email'),
            'url' => ['/user/account/change-email'],
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState('user', 'account', ['change-email', 'change-email-validate']),
            'isVisible' => Yii::$app->user->canChangeEmail()
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.base', 'Change Password'),
            'url' => ['/user/account/change-password'],
            'sortOrder' => 400,
            'isActive' => MenuLink::isActiveState('user', 'account', 'change-password'),
            'isVisible' => Yii::$app->user->canChangePassword()
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.base', 'Delete Account'),
            'url' => ['/user/account/delete'],
            'sortOrder' => 500,
            'isActive' => MenuLink::isActiveState('user', 'account', 'delete'),
            'isVisible' => Yii::$app->user->canDeleteAccount()
        ]));

        parent::init();
    }
}
