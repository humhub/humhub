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
 * Account Settings Tab Menu
 */
class AccountSettingsMenu extends PillTabs
{
    /**
     * @inheritdoc
     */
    public function init()
    {

        $this->addEntry(new MenuLink([
            'label' => Yii::t('UserModule.base', 'Basic Settings'),
            'url' => ['/user/account/edit-settings'],
            'sortOrder' => 100,
            'isActive' => ControllerHelper::isActivePath('user', 'account', 'edit-settings'),
        ]));

        parent::init();
    }
}
