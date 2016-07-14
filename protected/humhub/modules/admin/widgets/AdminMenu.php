<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;
use yii\helpers\Url;
use humhub\models\Setting;

/**
 * Description of AdminMenu
 *
 * @author luke
 */
class AdminMenu extends \humhub\widgets\BaseMenu
{

    public $template = "@humhub/widgets/views/leftNavigation";
    public $type = "adminNavigation";

    public function init()
    {

        $this->addItemGroup(array(
            'id' => 'manage',
            'label' => \Yii::t('AdminModule.widgets_AdminMenuWidget', '<strong>Administration</strong> menu'),
            'sortOrder' => 100,
        ));

        $this->addItem(array(
            'label' => \Yii::t('AdminModule.widgets_AdminMenuWidget', 'Users'),
            'url' => Url::toRoute(['/admin/user']),
            'icon' => '<i class="fa fa-user"></i>',
            'group' => 'manage',
            'sortOrder' => 200,
            'isActive' => (\Yii::$app->controller->module && \Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'user'),
            'isVisible' => \Yii::$app->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'User approval'),
            'id' => 'approval',
            'url' => Url::toRoute('/admin/approval'),
            'icon' => '<i class="fa fa-check-circle"></i>',
            'group' => 'manage',
            'sortOrder' => 201,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'approval'),
            'isVisible' => (Setting::Get('needApproval', 'authentication_internal') && Yii::$app->user->getIdentity()->canApproveUsers()),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Groups'),
            'id' => 'groups',
            'url' => Url::toRoute('/admin/group'),
            'icon' => '<i class="fa fa-group"></i>',
            'group' => 'manage',
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'group'),
            'sortOrder' => 300,
            'isVisible' => Yii::$app->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Spaces'),
            'id' => 'spaces',
            'url' => Url::toRoute('/admin/space'),
            'icon' => '<i class="fa fa-inbox"></i>',
            'group' => 'manage',
            'sortOrder' => 400,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'space'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Modules'),
            'id' => 'modules',
            'url' => Url::toRoute('/admin/module'),
            'icon' => '<i class="fa fa-rocket"></i>',
            'sortOrder' => 500,
            'group' => 'manage',
            'newItemCount' => 0,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'module'),
            'isVisible' => Yii::$app->user->isAdmin()
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'About'),
            'url' => Url::toRoute('/admin/about'),
            'icon' => '<i class="fa fa-info-circle"></i>',
            'sortOrder' => 10000,
            'group' => 'manage',
            'newItemCount' => 0,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'about'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));


        $this->addItemGroup(array(
            'id' => 'settings',
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Settings'),
            'sortOrder' => 200,
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Basic'),
            'url' => Url::toRoute('/admin/setting/index'),
            'icon' => '<i class="fa fa-cogs"></i>',
            'group' => 'settings',
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'basic'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Design'),
            'url' => Url::toRoute('/admin/setting/design'),
            'icon' => '<i class="fa fa-magic"></i>',
            'group' => 'settings',
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'design'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Authentication'),
            'url' => Url::toRoute('/admin/setting/authentication'),
            'icon' => '<i class="fa fa-lock"></i>',
            'group' => 'settings',
            'sortOrder' => 500,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && (Yii::$app->controller->action->id == 'authentication' || Yii::$app->controller->action->id == 'authenticationLdap')),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'User profiles'),
            'url' => Url::toRoute('/admin/user-profile/index'),
            'icon' => '<i class="fa fa-wrench"></i>',
            'group' => 'settings',
            'sortOrder' => 500,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'user-profile'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Mailing'),
            'url' => Url::toRoute('/admin/setting/mailing'),
            'icon' => '<i class="fa fa-envelope"></i>',
            'group' => 'settings',
            'sortOrder' => 600,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'mailing'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Caching'),
            'url' => Url::toRoute(['/admin/setting/caching']),
            'icon' => '<i class="fa fa-dashboard"></i>',
            'group' => 'settings',
            'sortOrder' => 800,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'caching'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Files'),
            'url' => Url::toRoute('/admin/setting/file'),
            'icon' => '<i class="fa fa-file"></i>',
            'group' => 'settings',
            'sortOrder' => 800,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'file'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Proxy'),
            'url' => Url::toRoute('/admin/setting/proxy'),
            'icon' => '<i class="fa fa-sitemap"></i>',
            'group' => 'settings',
            'sortOrder' => 800,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'proxy'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Statistics'),
            'url' => Url::toRoute('/admin/setting/statistic'),
            'icon' => '<i class="fa fa-bar-chart-o"></i>',
            'group' => 'settings',
            'sortOrder' => 900,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'statistic'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));
        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Cron jobs'),
            'url' => Url::toRoute('/admin/setting/cronjob'),
            'icon' => '<i class="fa fa-history"></i>',
            'group' => 'settings',
            'sortOrder' => 1000,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'cronjob'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Logging'),
            'url' => Url::toRoute('/admin/logging'),
            'icon' => '<i class="fa fa-keyboard-o"></i>',
            'group' => 'settings',
            'sortOrder' => 1100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'logging'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'OEmbed Provider'),
            'url' => Url::toRoute('/admin/setting/oembed'),
            'icon' => '<i class="fa fa-cloud"></i>',
            'group' => 'settings',
            'sortOrder' => 1200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && (Yii::$app->controller->action->id == 'oembed' || Yii::$app->controller->action->id == 'oembedEdit')),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));

        $this->addItem(array(
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Self test & update'),
            'url' => Url::toRoute('/admin/setting/self-test'),
            'icon' => '<i class="fa fa-warning"></i>',
            'group' => 'settings',
            'sortOrder' => 1300,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'selftest'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ));

        parent::init();
    }

}
