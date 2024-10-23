<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\helpers\ControllerHelper;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\TabMenu;
use Yii;

/**
 * Group Administration Menu
 */
class SettingsMenu extends TabMenu
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $canEditSettings = Yii::$app->user->can(ManageSettings::class);

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'General'),
            'url' => ['/admin/setting/index'],
            'sortOrder' => 100,
            'isActive' => ControllerHelper::isActivePath('admin', 'setting', 'basic'),
            'isVisible' => $canEditSettings,
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Appearance'),
            'url' => ['/admin/setting/design'],
            'sortOrder' => 200,
            'isActive' => ControllerHelper::isActivePath('admin', 'setting', 'design'),
            'isVisible' => $canEditSettings,
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Notifications'),
            'url' => ['/notification/admin/defaults'],
            'sortOrder' => 400,
            'isActive' => ControllerHelper::isActivePath('notification', 'admin', 'defaults'),
            'isVisible' => $canEditSettings,
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Topics'),
            'url' => ['/admin/setting/topics'],
            'sortOrder' => 600,
            'isActive' => ControllerHelper::isActivePath('admin', 'setting', ['topics', 'topic-edit']),
            'isVisible' => Yii::$app->user->isAdmin(),
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Advanced'),
            'url' => ['/admin/setting/advanced'],
            'sortOrder' => 1000,
            'isVisible' => $canEditSettings,
        ]));

        parent::init();
    }

}
