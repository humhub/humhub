<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;
use yii\helpers\Url;

/**
 * User Administration Menu
 *
 * @author Basti
 */
class UserMenu extends \humhub\widgets\BaseMenu
{

    public $template = "@humhub/widgets/views/tabMenu";
    public $type = "adminUserSubNavigation";

    public function init()
    {
        $this->addItem(array(
            'label' => Yii::t('AdminModule.views_user_index', 'Users'),
            'url' => Url::to(['/admin/user/index']),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'user'),
        ));
        
        $this->addItem(array(
            'label' => Yii::t('AdminModule.views_user_index', 'Settings'),
            'url' => Url::to(['/admin/authentication']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'authentication'),
        ));
        
        $approvalCount = \humhub\modules\admin\models\UserApprovalSearch::getUserApprovalCount();
        if($approvalCount > 0) {
            $this->addItem(array(
                'label' => Yii::t('AdminModule.user', 'Pending approvals') . ' <span class="label label-danger">'. $approvalCount .'</span>',
                'url' => Url::to(['/admin/approval']),
                'sortOrder' => 300,
                'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'approval'),
            ));
        }
        
        $this->addItem(array(
            'label' => Yii::t('AdminModule.user', 'Profiles'),
            'url' => Url::to(['/admin/user-profile']),
            'sortOrder' => 400,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'user-profile'),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.user', 'Groups'),
            'url' => Url::to(['/admin/group']),
            'sortOrder' => 500,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'group'),
        ));

        parent::init();
    }

}
