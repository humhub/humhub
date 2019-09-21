<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;
use humhub\modules\admin\models\UserApprovalSearch;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\TabMenu;

/**
 * User Administration Menu
 *
 * @author Basti
 */
class UserMenu extends TabMenu
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', 'Users'),
            'url' => ['/admin/user/index'],
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('admin', ['user', 'pending-registrations']),
            'isVisible' => Yii::$app->user->can([
                ManageUsers::class,
                ManageGroups::class,
            ])
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', 'Settings'),
            'url' => ['/admin/authentication'],
            'sortOrder' => 200,
            'isActive' => MenuLink::isActiveState('admin', 'authentication'),
            'isVisible' => Yii::$app->user->can(ManageSettings::class)
        ]));

        $approvalCount = UserApprovalSearch::getUserApprovalCount();

        if ($approvalCount > 0) {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('AdminModule.user', 'Pending approvals') . ' <span class="label label-danger">' . $approvalCount . '</span>',
                'url' => ['/admin/approval'],
                'sortOrder' => 300,
                'isActive' => MenuLink::isActiveState('admin', 'approval'),
                'isVisible' => Yii::$app->user->can([
                    ManageUsers::class,
                    ManageGroups::class
                ])
            ]));
        }

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', 'Profiles'),
            'url' => ['/admin/user-profile'],
            'sortOrder' => 400,
            'isActive' => MenuLink::isActiveState('admin', 'user-profile'),
            'isVisible' => Yii::$app->user->can(ManageUsers::class)
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', 'Groups'),
            'url' => ['/admin/group'],
            'sortOrder' => 500,
            'isActive' => MenuLink::isActiveState('admin', 'group'),
            'isVisible' => Yii::$app->user->can(ManageGroups::class)
        ]));

        parent::init();
    }

}
