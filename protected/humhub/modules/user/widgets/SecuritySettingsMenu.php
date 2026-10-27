<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\helpers\ControllerHelper;
use humhub\modules\user\widgets\security\SecurityDataSections;
use humhub\modules\user\widgets\security\SecurityLoginSections;
use humhub\modules\user\widgets\security\SecurityPrivacySections;
use humhub\modules\user\widgets\security\SecuritySessionSections;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\PillTabs;
use Yii;

/**
 * Tabs of the "Security & Privacy" account settings
 *
 * Modules add tabs on `EVENT_INIT`. Sort orders of the core tabs: Login 100, Devices & Access 300,
 * Privacy 400, My Data 900 - a two-factor authentication module should use 200.
 * Module pages render inside `@user/views/security/_layout.php`.
 * `EVENT_INIT` handlers may run several times per request (visibility checks) and must be cheap and side-effect free.
 *
 * @since 1.20
 */
class SecuritySettingsMenu extends PillTabs
{
    /**
     * @inheritdoc
     */
    public $renderSingleTab = true;

    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Login'),
            'url' => ['/user/security/login'],
            'sortOrder' => 100,
            'isActive' => ControllerHelper::isActivePath('user', 'security', 'login'),
            'isVisible' => (new SecurityLoginSections())->hasVisibleSections(),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Devices & Access'),
            'url' => ['/user/security/sessions'],
            'sortOrder' => 300,
            'isActive' => ControllerHelper::isActivePath('user', 'security', 'sessions'),
            'isVisible' => (new SecuritySessionSections())->hasVisibleSections(),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Privacy'),
            'url' => ['/user/security/privacy'],
            'sortOrder' => 400,
            'isActive' => ControllerHelper::isActivePath('user', 'security', 'privacy'),
            'isVisible' => (new SecurityPrivacySections())->hasVisibleSections(),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'My Data'),
            'url' => ['/user/security/data'],
            'sortOrder' => 900,
            'isActive' => ControllerHelper::isActivePath('user', 'security', ['data', 'delete']),
            'isVisible' => (new SecurityDataSections())->hasVisibleSections(),
        ]));

        parent::init();
    }

    /**
     * @return string|null the URL of the first visible tab
     */
    public function getFirstUrl(): ?string
    {
        $entry = $this->getFirstEntry(MenuLink::class, true);

        return $entry?->getUrl();
    }
}
