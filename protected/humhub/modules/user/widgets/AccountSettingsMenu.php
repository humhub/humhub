<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\helpers\ControllerHelper;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\PillTabs;
use Yii;

/**
 * Tabs of the "Account" settings
 *
 * Modules add tabs on `EVENT_INIT`. Sort orders of the core tabs: General 100, Modules 200, Topics 300 (topic
 * module). Module pages render inside `@user/views/account/_userSettingsLayout.php`.
 * `EVENT_INIT` handlers may run several times per request (visibility checks) and must be cheap and side-effect free.
 */
class AccountSettingsMenu extends PillTabs
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'General'),
            'url' => ['/user/account/edit-settings'],
            'sortOrder' => 100,
            'isActive' => ControllerHelper::isActivePath('user', 'account', ['edit-settings', 'change-email-validate']),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.account', 'Modules'),
            'url' => ['/user/account/edit-modules'],
            'sortOrder' => 200,
            'isActive' => ControllerHelper::isActivePath('user', 'account', 'edit-modules'),
            'isVisible' => count(Yii::$app->user->getIdentity()->moduleManager->getAvailable()) !== 0,
        ]));

        parent::init();
    }
}
