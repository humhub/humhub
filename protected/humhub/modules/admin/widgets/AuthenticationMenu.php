<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\SubTabMenu;
use Yii;
use yii\helpers\Url;

/**
 * Authentication Settings Menu
 */
class AuthenticationMenu extends SubTabMenu
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.setting', 'General'),
            'url' => ['/admin/authentication'],
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('admin', 'authentication', 'index'),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.setting', "LDAP"),
            'url' => ['/admin/authentication/authentication-ldap'],
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState('admin', 'authentication', 'authentication-ldap'),
        ]));

        parent::init();
    }

}
