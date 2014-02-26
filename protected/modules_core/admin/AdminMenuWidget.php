<?php

/**
 * Description of AdminNavigationWidget
 *
 * @package humhub.modules_core.admin
 * @since 0.5
 * @author Luke
 */
class AdminMenuWidget extends MenuWidget {

    public $template = "application.widgets.views.leftNavigation";
    public $type = "adminNavigation";

    public function init() {


        $this->addItemGroup(array(
            'id' => 'manage',
            'label' => Yii::t('AdminModule.base', 'Manage'),
            'sortOrder' => 100,
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'Users'),
            'url' => Yii::app()->createUrl('admin/user'),
            'icon' => '<i class="icon-user"></i>',
            'group' => 'manage',
            'sortOrder' => 200,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'user'),
            'isVisible' => Yii::app()->user->isAdmin(),
            'newItemCount' => 0
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'User approval'),
            'url' => Yii::app()->createUrl('admin/approval'),
            'icon' => '<i class="icon-ok-circle"></i>',
            'group' => 'manage',
            'sortOrder' => 200,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'approval'),
            'isVisible' => Yii::app()->user->canApproveUsers(),
            'newItemCount' => 0
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'Groups'),
            'url' => Yii::app()->createUrl('admin/group'),
            'icon' => '<i class="icon-group"></i>',
            'group' => 'manage',
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'group'),
            'sortOrder' => 300,
            'isVisible' => Yii::app()->user->isAdmin(),
            'newItemCount' => 0
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'Modules'),
            'url' => Yii::app()->createUrl('admin/module'),
            'icon' => '<i class="icon-rocket"></i>',
            'sortOrder' => 400,
            'group' => 'manage',
            'newItemCount' => 0,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'module'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'About'),
            'url' => Yii::app()->createUrl('admin/about'),
            'icon' => '<i class="icon-info-sign"></i>',
            'sortOrder' => 10000,
            'group' => 'manage',
            'newItemCount' => 0,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'about'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));


        $this->addItemGroup(array(
            'id' => 'settings',
            'label' => Yii::t('AdminModule.base', 'Settings'),
            'sortOrder' => 200,
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'Basic'),
            'url' => Yii::app()->createUrl('admin/setting/index'),
            'icon' => '<i class="icon-cogs"></i>',
            'group' => 'settings',
            'sortOrder' => 100,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'index'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'Design'),
            'url' => Yii::app()->createUrl('admin/setting/design'),
            'icon' => '<i class="icon-magic"></i>',
            'group' => 'settings',
            'sortOrder' => 200,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'design'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        /*
          $this->addItem(array(
          'label' => Yii::t('AdminModule.base', 'Users'),
          'url' => Yii::app()->createUrl('admin/setting/user'),
          'group' => 'settings',
          'sortOrder' => 300,
          'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'authentication'),
          ));
          $this->addItem(array(
          'label' => Yii::t('AdminModule.base', 'Spaces'),
          'url' => Yii::app()->createUrl('admin/setting/user'),
          'group' => 'settings',
          'sortOrder' => 400,
          'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'authentication'),
          ));
         *
         */
        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'Authentication'),
            'url' => Yii::app()->createUrl('admin/setting/authentication'),
            'icon' => '<i class="icon-lock"></i>',
            'group' => 'settings',
            'sortOrder' => 500,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && (Yii::app()->controller->action->id == 'authentication' || Yii::app()->controller->action->id == 'authenticationLdap')),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'User profiles'),
            'url' => Yii::app()->createUrl('admin/userprofile/index'),
            'icon' => '<i class="icon-wrench"></i>',
            'group' => 'settings',
            'sortOrder' => 500,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'userprofile'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        /*
          $this->addItem(array(
          'label' => Yii::t('AdminModule.base', 'Security & Roles'),
          'url' => Yii::app()->createUrl('admin/setting/security'),
          'group' => 'settings',
          'sortOrder' => 700,
          'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'security'),
          ));

         */
        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'Mailing'),
            'url' => Yii::app()->createUrl('admin/setting/mailing'),
            'icon' => '<i class="icon-envelope"></i>',
            'group' => 'settings',
            'sortOrder' => 600,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'mailing'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'Caching'),
            'url' => Yii::app()->createUrl('admin/setting/caching'),
            'icon' => '<i class="icon-dashboard"></i>',
            'group' => 'settings',
            'sortOrder' => 800,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'caching'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'Files'),
            'url' => Yii::app()->createUrl('admin/setting/file'),
            'icon' => '<i class="icon-file"></i>',
            'group' => 'settings',
            'sortOrder' => 800,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'file'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'Statistics'),
            'url' => Yii::app()->createUrl('admin/setting/statistic'),
            'icon' => '<i class="icon-bar-chart"></i>',
            'group' => 'settings',
            'sortOrder' => 900,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'statistic'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'Cron jobs'),
            'url' => Yii::app()->createUrl('admin/setting/cronjob'),
            'icon' => '<i class="icon-truck"></i>',
            'group' => 'settings',
            'sortOrder' => 1000,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'cronjob'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.base', 'Self test & update'),
            'url' => Yii::app()->createUrl('admin/setting/selftest'),
            'icon' => '<i class="icon-warning-sign"></i>',
            'group' => 'settings',
            'sortOrder' => 1100,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'selftest'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));

        parent::init();
    }

}

?>
