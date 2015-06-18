<?php

/**
 * AccountMenuWidget as (usally left) navigation on users account options.
 * 
 * @package humhub.modules_core.user.widgets
 * @since 0.5
 * @author Luke
 */
class AccountMenuWidget extends MenuWidget {

    public $template = "application.widgets.views.leftNavigation";
    public $type = "accountNavigation";

    public function init() {

        $this->addItemGroup(array(
            'id' => 'account',
            'label' => Yii::t('UserModule.widgets_AccountMenuWidget', '<strong>Account</strong> settings'),
            'sortOrder' => 100,
        ));

        $this->addItem(array(
            'label' => Yii::t('UserModule.widgets_AccountMenuWidget', 'Profile'),
            'icon' => '<i class="fa fa-user"></i>',
            'group' => 'account',
            'url' => Yii::app()->createUrl('//user/account/edit'),
            'sortOrder' => 100,
            'isActive' => (Yii::app()->controller->action->id == "edit"),
        ));

        $this->addItem(array(
            'label' => Yii::t('UserModule.widgets_AccountMenuWidget', 'Settings'),
            'icon' => '<i class="fa fa-wrench"></i>',
            'group' => 'account',
            'url' => Yii::app()->createUrl('//user/account/editSettings'),
            'sortOrder' => 110,
            'isActive' => (Yii::app()->controller->action->id == "editSettings"),
        ));

        
        // Only show this page when really user specific modules available
        if (count(Yii::app()->user->getModel()->getAvailableModules()) != 0) {
            $this->addItem(array(
                'label' => Yii::t('UserModule.widgets_AccountMenuWidget', 'Modules'),
                'icon' => '<i class="fa fa-rocket"></i>',
                'group' => 'account',
                'url' => Yii::app()->createUrl('//user/account/editModules'),
                'sortOrder' => 120,
                'isActive' => (Yii::app()->controller->action->id == "editModules"),
            ));
        }
 
        $this->addItem(array(
            'label' => Yii::t('UserModule.widgets_AccountMenuWidget', 'Notifications'),
            'icon' => '<i class="fa fa-bell"></i>',
            'group' => 'account',
            'url' => Yii::app()->createUrl('//user/account/emailing/'),
            'sortOrder' => 200,
            'isActive' => (Yii::app()->controller->action->id == "emailing"),
        ));

        // LDAP users cannot change their e-mail address
        if (Yii::app()->user->getAuthMode() != User::AUTH_MODE_LDAP) {
        	$this->addItem(array(
        			'label' => Yii::t('UserModule.widgets_AccountMenuWidget', 'Username'),
        			'icon' => '<i class="fa fa-pencil"></i>',
        			'group' => 'account',
        			'url' => Yii::app()->createUrl('//user/account/changeUsername/'),
        			'sortOrder' => 250,
        			'isActive' => (Yii::app()->controller->action->id == "changeUsername"),
        	));
            $this->addItem(array(
                'label' => Yii::t('UserModule.widgets_AccountMenuWidget', 'E-Mail'),
                'icon' => '<i class="fa fa-paper-plane"></i>',
                'group' => 'account',
                'url' => Yii::app()->createUrl('//user/account/changeEmail'),
                'sortOrder' => 300,
                'isActive' => (Yii::app()->controller->action->id == "changeEmail"),
            ));
        }

        // LDAP users cannot changes password or delete account
        if (Yii::app()->user->getAuthMode() != User::AUTH_MODE_LDAP) {
            $this->addItem(array(
                'label' => Yii::t('UserModule.widgets_AccountMenuWidget', 'Password'),
                'icon' => '<i class="fa fa-key"></i>',
                'group' => 'account',
                'url' => Yii::app()->createUrl('//user/account/changePassword'),
                'sortOrder' => 500,
                'isActive' => (Yii::app()->controller->action->id == "changePassword"),
            ));
            $this->addItem(array(
                'label' => Yii::t('UserModule.widgets_AccountMenuWidget', 'Delete account'),
                'icon' => '<i class="fa fa-trash-o"></i>',
                'group' => 'account',
                'url' => Yii::app()->createUrl('//user/account/delete'),
                'sortOrder' => 600,
                'isActive' => (Yii::app()->controller->action->id == "delete"),
            ));
        }

        parent::init();
    }

}

?>
