<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\SubTabMenu;

/**
 * Group Administration Menu
 */
class GroupManagerMenu extends SubTabMenu
{

    /**
     * @var \humhub\modules\user\models\Group
     */
    public $group;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', 'Settings'),
            'url' => ['/admin/group/edit', 'id' => $this->group->id],
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('admin', 'group', 'edit')
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', "Permissions"),
            'url' => ['/admin/group/manage-permissions', 'id' => $this->group->id],
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState('admin', 'group', 'manage-permissions')
        ]));


        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', "Members"),
            'url' => ['/admin/group/manage-group-users', 'id' => $this->group->id],
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState('admin', 'group', 'manage-group-users')
        ]));

        parent::init();
    }

}
