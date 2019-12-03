<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\modules\ui\menu\MenuLink;
use Yii;
use yii\helpers\Url;
use humhub\modules\ui\menu\widgets\SubTabMenu;

/**
 * Authentication Settings Menu
 */
class AdvancedSettingMenu extends SubTabMenu
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Caching'),
            'url' => Url::toRoute(['/admin/setting/caching']),
            'icon' => 'dashboard',
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState('admin', 'setting', 'caching'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ]));

         $this->addEntry(new MenuLink([
             'label' => Yii::t('AdminModule.base', 'Files'),
             'url' => Url::toRoute('/admin/setting/file'),
             'icon' => 'file',
             'sortOrder' => 200,
             'isActive' =>  MenuLink::isActiveState('admin', 'setting', 'file'),
             'isVisible' => Yii::$app->user->isAdmin(),
         ]));

         $this->addEntry(new MenuLink([
             'label' => Yii::t('AdminModule.settings', 'E-Mail'),
             'url' => Url::toRoute(['/admin/setting/mailing-server']),
             'icon' => 'envelope',
             'sortOrder' => 250,
             'isActive' => MenuLink::isActiveState('admin', 'setting', 'mailing-server'),
             'isVisible' => Yii::$app->user->isAdmin(),
         ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Proxy'),
            'url' => Url::toRoute('/admin/setting/proxy'),
            'icon' => 'sitemap',
            'sortOrder' => 300,
            'isActive' => MenuLink::isActiveState('admin', 'setting', 'proxy'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Statistics'),
            'url' => Url::toRoute('/admin/setting/statistic'),
            'icon' => 'bar-chart-o',
            'sortOrder' => 400,
            'isActive' => MenuLink::isActiveState('admin', 'setting', 'statistic'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'OEmbed'),
            'url' => Url::toRoute('/admin/setting/oembed'),
            'icon' => 'cloud',
            'sortOrder' => 500,
            'isActive' => MenuLink::isActiveState('admin', 'setting', 'oembed'),
            'isVisible' => Yii::$app->user->isAdmin(),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Logs'),
            'url' => Url::toRoute('/admin/setting/logs'),
            'icon' => 'terminal',
            'sortOrder' => 600,
            'isActive' => MenuLink::isActiveState('admin', 'setting', ['logs', 'logs-edit']),
            'isVisible' => Yii::$app->user->isAdmin(),
        ]));

        parent::init();
    }

}
