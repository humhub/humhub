<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\SubTabMenu;
use Yii;
use yii\helpers\Url;

/**
 * Authentication Settings Menu
 *
 * @TODO Refactor/Rename to UserSettingsMenu
 */
class AuthenticationMenu extends SubTabMenu
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.settings', 'General'),
            'url' => ['/admin/authentication'],
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('admin', 'authentication', 'index'),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', 'Profile Permissions'),
            'url' => ['/admin/user-permissions'],
            'sortOrder' => 600,
            'isActive' => MenuLink::isActiveState('admin', 'user-permissions'),
            'isVisible' => Yii::$app->user->can(ManageGroups::class)
        ]));

        parent::init();
    }

}
