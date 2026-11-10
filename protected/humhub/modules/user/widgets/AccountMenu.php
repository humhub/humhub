<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\helpers\ControllerHelper;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\SectionMenu;
use Yii;

/**
 * The left menu of the account settings
 *
 * Modules add entries on `EVENT_INIT`. Sort orders of the core entries: Profile 100, Account 200,
 * Notifications & E-Mails 300, Appearance & Accessibility 400, Security & Privacy 500; the friendship module adds
 * Friends at 600. A module entry should use 1000 or more, so it comes after the core entries. A module page
 * rendered inside one of the core layouts marks its entry active by id, e.g.
 * `AccountMenu::markAsActive('account-settings-settings')`.
 *
 * @since 0.5
 * @author Luke
 */
class AccountMenu extends SectionMenu
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->title = Yii::t('UserModule.account', 'My Settings');

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Profile'),
            'id' => 'account-settings-profile',
            'icon' => 'user',
            'url' => ['/user/account/edit'],
            'sortOrder' => 100,
            'isActive' => ControllerHelper::isActivePath('user', 'account', 'edit'),
        ]));

        $settingsTabs = new AccountSettingsMenu();
        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Account'),
            'id' => 'account-settings-settings',
            'icon' => 'settings',
            'url' => $settingsTabs->getFirstUrl(),
            'sortOrder' => 200,
            'isActive' => ControllerHelper::isActivePath('user', 'account', ['edit-settings', 'edit-modules', 'change-username', 'change-email', 'change-email-validate']),
            'isVisible' => $settingsTabs->getFirstUrl() !== null,
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Notifications & E-Mails'),
            'id' => 'account-settings-notifications',
            'icon' => 'bell',
            'url' => ['/notification/user'],
            'sortOrder' => 300,
            'isActive' => ControllerHelper::isActivePath('notification', 'user'),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Appearance & Accessibility'),
            'id' => 'account-settings-appearance',
            'icon' => 'paint-brush',
            'url' => ['/user/appearance/index'],
            'sortOrder' => 400,
            'isActive' => ControllerHelper::isActivePath('user', 'appearance'),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Security & Privacy'),
            'id' => 'account-settings-security',
            'icon' => 'shield',
            'url' => ['/user/security/index'],
            'sortOrder' => 500,
            'isActive' => ControllerHelper::isActivePath('user', 'security'),
            'isVisible' => (new SecuritySettingsMenu())->getFirstUrl() !== null,
        ]));

        parent::init();
    }
}
