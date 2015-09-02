<?php

/**
 * Description of AdminNavigationWidget
 *
 * @package humhub.modules_core.admin
 * @since 0.5
 * @author Luke
 */
class AdminMenuWidget extends MenuWidget
{

    public $template = "application.widgets.views.leftNavigation";
    public $type = "adminNavigation";

    public function init()
    {


        $this->addItemGroup(array(
            'id' => 'manage',
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', '<strong>Administration</strong> menu'),
            'sortOrder' => 100,
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Users'),
            'url' => Yii::app()->createUrl('admin/user'),
            'icon' => '<i class="fa fa-user"></i>',
            'group' => 'manage',
            'sortOrder' => 200,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'user'),
            'isVisible' => Yii::app()->user->isAdmin(),
            'newItemCount' => 0
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'User approval'),
            'id' => 'approval',
            'url' => Yii::app()->createUrl('admin/approval'),
            'icon' => '<i class="fa fa-check-circle"></i>',
            'group' => 'manage',
            'sortOrder' => 200,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'approval'),
            'isVisible' => Yii::app()->user->canApproveUsers(),
            'newItemCount' => 0
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Groups'),
            'id' => 'groups',
            'url' => Yii::app()->createUrl('admin/group'),
            'icon' => '<i class="fa fa-group"></i>',
            'group' => 'manage',
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'group'),
            'sortOrder' => 300,
            'isVisible' => Yii::app()->user->isAdmin(),
            'newItemCount' => 0
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Spaces'),
            'id' => 'spaces',
            'url' => Yii::app()->createUrl('admin/space'),
            'icon' => '<i class="fa fa-inbox"></i>',
            'group' => 'manage',
            'sortOrder' => 400,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'space'),
            'isVisible' => Yii::app()->user->isAdmin(),
            'newItemCount' => 0
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Modules'),
            'id' => 'modules',
            'url' => Yii::app()->createUrl('admin/module'),
            'icon' => '<i class="fa fa-rocket"></i>',
            'sortOrder' => 500,
            'group' => 'manage',
            'newItemCount' => 0,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'module'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'About'),
            'url' => Yii::app()->createUrl('admin/about'),
            'icon' => '<i class="fa fa-info-circle"></i>',
            'sortOrder' => 10000,
            'group' => 'manage',
            'newItemCount' => 0,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'about'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));


        $this->addItemGroup(array(
            'id' => 'settings',
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Settings'),
            'sortOrder' => 200,
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Basic'),
            'url' => Yii::app()->createUrl('admin/setting/index'),
            'icon' => '<i class="fa fa-cogs"></i>',
            'group' => 'settings',
            'sortOrder' => 100,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'index'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Design'),
            'url' => Yii::app()->createUrl('admin/setting/design'),
            'icon' => '<i class="fa fa-magic"></i>',
            'group' => 'settings',
            'sortOrder' => 200,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'design'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        /*
          $this->addItem(array(
          'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Users'),
          'url' => Yii::app()->createUrl('admin/setting/user'),
          'group' => 'settings',
          'sortOrder' => 300,
          'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'authentication'),
          ));
          $this->addItem(array(
          'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Spaces'),
          'url' => Yii::app()->createUrl('admin/setting/user'),
          'group' => 'settings',
          'sortOrder' => 400,
          'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'authentication'),
          ));
         *
         */
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Authentication'),
            'url' => Yii::app()->createUrl('admin/setting/authentication'),
            'icon' => '<i class="fa fa-lock"></i>',
            'group' => 'settings',
            'sortOrder' => 500,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && (Yii::app()->controller->action->id == 'authentication' || Yii::app()->controller->action->id == 'authenticationLdap')),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'User profiles'),
            'url' => Yii::app()->createUrl('admin/userprofile/index'),
            'icon' => '<i class="fa fa-wrench"></i>',
            'group' => 'settings',
            'sortOrder' => 500,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'userprofile'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        /*
          $this->addItem(array(
          'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Security & Roles'),
          'url' => Yii::app()->createUrl('admin/setting/security'),
          'group' => 'settings',
          'sortOrder' => 700,
          'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'security'),
          ));

         */
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Mailing'),
            'url' => Yii::app()->createUrl('admin/setting/mailing'),
            'icon' => '<i class="fa fa-envelope"></i>',
            'group' => 'settings',
            'sortOrder' => 600,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'mailing'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Caching'),
            'url' => Yii::app()->createUrl('admin/setting/caching'),
            'icon' => '<i class="fa fa-dashboard"></i>',
            'group' => 'settings',
            'sortOrder' => 800,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'caching'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Files'),
            'url' => Yii::app()->createUrl('admin/setting/file'),
            'icon' => '<i class="fa fa-file"></i>',
            'group' => 'settings',
            'sortOrder' => 800,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'file'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Proxy'),
            'url' => Yii::app()->createUrl('admin/setting/proxy'),
            'icon' => '<i class="fa fa-sitemap"></i>',
            'group' => 'settings',
            'sortOrder' => 800,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'proxy'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Statistics'),
            'url' => Yii::app()->createUrl('admin/setting/statistic'),
            'icon' => '<i class="fa fa-bar-chart-o"></i>',
            'group' => 'settings',
            'sortOrder' => 900,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'statistic'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Cron jobs'),
            'url' => Yii::app()->createUrl('admin/setting/cronjob'),
            'icon' => '<i class="fa fa-history"></i>',
            'group' => 'settings',
            'sortOrder' => 1000,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'cronjob'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Logging'),
            'url' => Yii::app()->createUrl('admin/logging'),
            'icon' => '<i class="fa fa-keyboard-o"></i>',
            'group' => 'settings',
            'sortOrder' => 1100,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'logging'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));


        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'OEmbed Provider'),
            'url' => Yii::app()->createUrl('admin/setting/oembed'),
            'icon' => '<i class="fa fa-cloud"></i>',
            'group' => 'settings',
            'sortOrder' => 1200,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && (Yii::app()->controller->action->id == 'oembed' || Yii::app()->controller->action->id == 'oembedEdit')),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));
        
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Self test & update'),
            'url' => Yii::app()->createUrl('admin/setting/selftest'),
            'icon' => '<i class="fa fa-warning"></i>',
            'group' => 'settings',
            'sortOrder' => 1300,
            'isActive' => (Yii::app()->controller->module && Yii::app()->controller->module->id == 'admin' && Yii::app()->controller->id == 'setting' && Yii::app()->controller->action->id == 'selftest'),
            'isVisible' => Yii::app()->user->isAdmin(),
        ));

        parent::init();
    }

}

?>
