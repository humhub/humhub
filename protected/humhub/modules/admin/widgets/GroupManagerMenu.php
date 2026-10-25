<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\helpers\ControllerHelper;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\user\models\Group;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\PillTabs;
use Yii;

/**
 * Group Administration Menu
 */
class GroupManagerMenu extends PillTabs
{
    /**
     * @inheritdoc
     */
    public string $variant = self::VARIANT_SECONDARY;

    /**
     * @var Group
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
            'isActive' => ControllerHelper::isActivePath('admin', 'group', 'edit'),
        ]));

        if (Yii::$app->user->can(ManageGroups::class)) {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('AdminModule.user', 'Permissions'),
                'url' => ['/admin/group/manage-permissions', 'id' => $this->group->id],
                'sortOrder' => 200,
                'isActive' => ControllerHelper::isActivePath('admin', 'group', 'manage-permissions'),
            ]));
        }

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', 'Members'),
            'url' => ['/admin/group/manage-group-users', 'id' => $this->group->id],
            'sortOrder' => 300,
            'isActive' => ControllerHelper::isActivePath('admin', 'group', 'manage-group-users'),
        ]));

        parent::init();
    }

}
