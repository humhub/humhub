<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\helpers\ControllerHelper;
use humhub\modules\admin\models\UserApprovalSearch;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\TabMenu;
use Yii;

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
            'label' => Yii::t('AdminModule.user', 'Overview'),
            'url' => ['/admin/user/index'],
            'sortOrder' => 100,
            'isActive' => ControllerHelper::isActivePath('admin', ['user', 'pending-registrations']),
            'isVisible' => Yii::$app->user->can([
                ManageUsers::class,
                ManageGroups::class,
            ]),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', 'Settings'),
            'url' => ['/admin/authentication'],
            'sortOrder' => 200,
            'isActive' => ControllerHelper::isActivePath('admin', ['authentication', 'user-permissions'])
                || ControllerHelper::isActivePath('ldap', 'admin'),
            'isVisible' => Yii::$app->user->can(ManageSettings::class),
        ]));

        $approvalCount = UserApprovalSearch::getUserApprovalCount();

        if ($approvalCount > 0) {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('AdminModule.user', 'Pending approvals') . ' <span class="label label-danger">' . $approvalCount . '</span>',
                'url' => ['/admin/approval'],
                'sortOrder' => 300,
                'isActive' => ControllerHelper::isActivePath('admin', 'approval'),
                'isVisible' => Yii::$app->user->can([
                    ManageUsers::class,
                    ManageGroups::class,
                ]),
            ]));
        }

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', 'Profiles'),
            'url' => ['/admin/user-profile'],
            'sortOrder' => 400,
            'isActive' => ControllerHelper::isActivePath('admin', 'user-profile'),
            'isVisible' => Yii::$app->user->can(ManageUsers::class),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', 'Groups'),
            'url' => ['/admin/group'],
            'sortOrder' => 500,
            'isActive' => ControllerHelper::isActivePath('admin', 'group'),
            'isVisible' => Yii::$app->user->can(ManageGroups::class),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.user', 'People'),
            'url' => ['/admin/user-people'],
            'sortOrder' => 600,
            'isActive' => ControllerHelper::isActivePath('admin', 'user-people'),
            'isVisible' => Yii::$app->user->can(ManageSettings::class),
        ]));


        parent::init();
    }

}
