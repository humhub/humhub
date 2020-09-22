<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\TabMenu;

/**
 * User Administration Menu
 *
 * @author Basti
 */
class UserSettingMenu extends TabMenu
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', 'General'),
            'url' => ['/admin/setting/authentication'],
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('admin', 'settings', 'authentication'),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', 'LDAP'),
            'url' => ['/admin/setting/authentication-ldap'],
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState('admin', 'settings', 'authentication-ldap'),
        ]));

        parent::init();
    }

}
