<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use Yii;
use \humhub\widgets\BaseMenu;
use \yii\helpers\Url;

/**
 * AccountMenuWidget as (usally left) navigation on users account options.
 *
 * @package humhub.modules_core.user.widgets
 * @since 0.5
 * @author Luke
 */
class AccountMenu extends BaseMenu
{

    public $template = "@humhub/widgets/views/leftNavigation";
    public $type = "accountNavigation";

    public function init()
    {

        $controllerAction = Yii::$app->controller->action->id;
        $this->addItemGroup(array(
            'id' => 'account',
            'label' => Yii::t('UserModule.widgets_AccountMenuWidget', '<strong>Account</strong> settings'),
            'sortOrder' => 100,
        ));

        $this->addItem(array(
            'label' => Yii::t('UserModule.widgets_AccountMenuWidget', 'Profile'),
            'icon' => '<i class="fa fa-user"></i>',
            'group' => 'account',
            'url' => Url::toRoute('/user/account/edit'),
            'sortOrder' => 100,
            'isActive' => ($controllerAction == "edit" || $controllerAction == "change-email" || $controllerAction == "change-password" || $controllerAction == "delete"),
        ));

        $this->addItem(array(
            'label' => Yii::t('UserModule.account', 'E-Mail Summaries'),
            'icon' => '<i class="fa fa-envelope"></i>',
            'group' => 'account',
            'url' => Url::toRoute('/activity/user'),
            'sortOrder' => 105,
            'isActive' => (Yii::$app->controller->module->id == 'activity'),
        ));
        
        $this->addItem(array(
            'label' => Yii::t('UserModule.account', 'Notifications'),
            'icon' => '<i class="fa fa-bell"></i>',
            'group' => 'account',
            'url' => Url::toRoute('/notification/user'),
            'sortOrder' => 106,
            'isActive' => (Yii::$app->controller->module->id == 'notification'),
        ));

        $this->addItem(array(
            'label' => Yii::t('UserModule.widgets_AccountMenuWidget', 'Settings'),
            'icon' => '<i class="fa fa-wrench"></i>',
            'group' => 'account',
            'url' => Url::toRoute('/user/account/edit-settings'),
            'sortOrder' => 110,
            'isActive' => ($controllerAction == "edit-settings"),
        ));

        $this->addItem(array(
            'label' => Yii::t('UserModule.widgets_AccountMenuWidget', 'Security'),
            'icon' => '<i class="fa fa-lock"></i>',
            'group' => 'account',
            'url' => Url::toRoute('/user/account/security'),
            'sortOrder' => 115,
            'isActive' => (Yii::$app->controller->action->id == "security"),
        ));

        // Only show this page when really user specific modules available
        if (count(Yii::$app->user->getIdentity()->getAvailableModules()) != 0) {
            $this->addItem(array(
                'label' => Yii::t('UserModule.widgets_AccountMenuWidget', 'Modules'),
                'icon' => '<i class="fa fa-rocket"></i>',
                'group' => 'account',
                'url' => Url::toRoute('//user/account/edit-modules'),
                'sortOrder' => 120,
                'isActive' => (Yii::$app->controller->action->id == "edit-modules"),
            ));
        }

        parent::init();
    }

}

?>
