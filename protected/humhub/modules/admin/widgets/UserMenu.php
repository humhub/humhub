<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;
use yii\helpers\Url;
use humhub\modules\admin\models\UserApprovalSearch;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\user\models\Invite;
use humhub\widgets\BaseMenu;

/**
 * User Administration Menu
 *
 * @author Basti
 */
class UserMenu extends BaseMenu
{

    public $template = '@humhub/widgets/views/tabMenu';
    public $type = 'adminUserSubNavigation';

    public function init()
    {
        $this->addItem([
            'label' => Yii::t('AdminModule.views_user_index', 'Users'),
            'url' => Url::to(['/admin/user/index']),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && (Yii::$app->controller->id == 'user' || Yii::$app->controller->id == 'pending-registrations')),
            'isVisible' => Yii::$app->user->can([
                new ManageUsers(),
                new ManageGroups(),
            ])
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.views_user_index', 'Settings'),
            'url' => Url::to(['/admin/authentication']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'authentication'),
            'isVisible' => Yii::$app->user->can([
                new ManageSettings()
            ])
        ]);

        $approvalCount = UserApprovalSearch::getUserApprovalCount();
        if ($approvalCount > 0) {
            $this->addItem([
                'label' => Yii::t('AdminModule.user', 'Pending approvals') . ' <span class="label label-danger">' . $approvalCount . '</span>',
                'url' => Url::to(['/admin/approval']),
                'sortOrder' => 300,
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'approval'),
                'isVisible' => Yii::$app->user->can([
                    new ManageUsers(),
                    new ManageGroups()
                ])
            ]);
        }

        $this->addItem([
            'label' => Yii::t('AdminModule.user', 'Profiles'),
            'url' => Url::to(['/admin/user-profile']),
            'sortOrder' => 400,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'user-profile'),
            'isVisible' => Yii::$app->user->can([
                new ManageUsers()
            ])
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.user', 'Groups'),
            'url' => Url::to(['/admin/group']),
            'sortOrder' => 500,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'group'),
            'isVisible' => Yii::$app->user->can(
                    new ManageGroups()
            )
        ]);

        parent::init();
    }

}
