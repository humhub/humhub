<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use Yii;
use yii\helpers\Url;

/**
 * Authentication Settings Menu
 */
class AdvancedSettingMenu extends \humhub\widgets\BaseMenu
{

    /**
     * @inheritdoc
     */
    public $template = "@humhub/widgets/views/subTabMenu";

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Caching'),
            'url' => Url::toRoute(['/admin/setting/caching']),
            'icon' => '<i class="fa fa-dashboard"></i>',
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'caching'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Files'),
            'url' => Url::toRoute('/admin/setting/file'),
            'icon' => '<i class="fa fa-file"></i>',
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'file'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.views_setting_mailing', 'E-Mail'),
            'url' => Url::toRoute(['/admin/setting/mailing-server']),
            'icon' => '<i class="fa fa-envelope"></i>',
            'sortOrder' => 250,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'mailing-server'),
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Proxy'),
            'url' => Url::toRoute('/admin/setting/proxy'),
            'icon' => '<i class="fa fa-sitemap"></i>',
            'sortOrder' => 300,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'proxy'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ]);
        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Statistics'),
            'url' => Url::toRoute('/admin/setting/statistic'),
            'icon' => '<i class="fa fa-bar-chart-o"></i>',
            'sortOrder' => 400,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && Yii::$app->controller->action->id == 'statistic'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'OEmbed'),
            'url' => Url::toRoute('/admin/setting/oembed'),
            'icon' => '<i class="fa fa-cloud"></i>',
            'sortOrder' => 500,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && (Yii::$app->controller->action->id == 'oembed' || Yii::$app->controller->action->id == 'oembed-edit')),
            'isVisible' => Yii::$app->user->isAdmin(),
        ]);

        $this->addItem([
            'label' => Yii::t('AdminModule.widgets_AdminMenuWidget', 'Logs'),
            'url' => Url::toRoute('/admin/setting/logs'),
            'icon' => '<i class="fa fa-terminal"></i>',
            'sortOrder' => 600,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'admin' && Yii::$app->controller->id == 'setting' && (Yii::$app->controller->action->id == 'logs' || Yii::$app->controller->action->id == 'logs-edit')),
            'isVisible' => Yii::$app->user->isAdmin(),
        ]);

        parent::init();
    }

}
