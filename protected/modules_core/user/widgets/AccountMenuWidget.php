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
            'id' => 'admin',
            'label' => Yii::t('UserModule.base', 'Account settings'),
            'sortOrder' => 100,
        ));

        $this->addItem(array(
            'label' => Yii::t('UserModule.base', 'Edit Profile'),
            'url' => Yii::app()->createUrl('//user/account/edit'),
            'sortOrder' => 100,
            'isActive' => (Yii::app()->controller->action->id == "edit"),
        ));

        $this->addItem(array(
            'label' => Yii::t('UserModule.base', 'Edit Settings'),
            'url' => Yii::app()->createUrl('//user/account/editSettings'),
            'sortOrder' => 110,
            'isActive' => (Yii::app()->controller->action->id == "editSettings"),
        ));

        // Only show this page when really user specific modules available
        if (count(Yii::app()->user->getModel()->getAvailableModules()) != 0) {
            $this->addItem(array(
                'label' => Yii::t('UserModule.base', 'Edit Modules'),
                'url' => Yii::app()->createUrl('//user/account/editModules'),
                'sortOrder' => 120,
                'isActive' => (Yii::app()->controller->action->id == "editModules"),
            ));
        }
 
        $this->addItem(array(
            'label' => Yii::t('UserModule.base', 'E-Mail notifications'),
            'url' => Yii::app()->createUrl('//user/account/emailing/'),
            'sortOrder' => 200,
            'isActive' => (Yii::app()->controller->action->id == "emailing"),
        ));

        // LDAP users cannot change their e-mail address
        if (Yii::app()->user->getAuthMode() != User::AUTH_MODE_LDAP) {
            $this->addItem(array(
                'label' => Yii::t('UserModule.base', 'Change E-Mail'),
                'url' => Yii::app()->createUrl('//user/account/changeEmail'),
                'sortOrder' => 300,
                'isActive' => (Yii::app()->controller->action->id == "changeEmail"),
            ));
        }
        $this->addItem(array(
            'label' => Yii::t('UserModule.base', 'Change image'),
            'url' => Yii::app()->createUrl('//user/account/changeImage'),
            'sortOrder' => 400,
            'isActive' => (Yii::app()->controller->action->id == "changeImage"),
        ));

        // LDAP users cannot changes password or delete account
        if (Yii::app()->user->getAuthMode() != User::AUTH_MODE_LDAP) {
            $this->addItem(array(
                'label' => Yii::t('UserModule.base', 'Change password'),
                'url' => Yii::app()->createUrl('//user/account/changePassword'),
                'sortOrder' => 500,
                'isActive' => (Yii::app()->controller->action->id == "changePassword"),
            ));
            $this->addItem(array(
                'label' => Yii::t('UserModule.base', 'Delete your account'),
                'url' => Yii::app()->createUrl('//user/account/delete'),
                'sortOrder' => 600,
                'isActive' => (Yii::app()->controller->action->id == "delete"),
            ));
        }

        parent::init();
    }

}

?>
