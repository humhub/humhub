<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\helpers\ControllerHelper;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\PillTabs;
use Yii;

/**
 * Tabs of Administration → Appearance
 *
 * @since 1.20
 */
class AppearanceMenu extends PillTabs
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $canEditSettings = Yii::$app->user->can(ManageSettings::class);

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'General'),
            'url' => ['/admin/appearance/index'],
            'sortOrder' => 100,
            'isActive' => ControllerHelper::isActivePath('admin', 'appearance', 'index'),
            'isVisible' => $canEditSettings,
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Logo & Images'),
            'url' => ['/admin/appearance/images'],
            'sortOrder' => 200,
            'isActive' => ControllerHelper::isActivePath('admin', 'appearance', 'images'),
            'isVisible' => $canEditSettings,
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Theme'),
            'url' => ['/admin/appearance/theme'],
            'sortOrder' => 300,
            'isActive' => ControllerHelper::isActivePath('admin', 'appearance', 'theme'),
            'isVisible' => $canEditSettings,
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Dark Mode'),
            'url' => ['/admin/appearance/dark-mode'],
            'sortOrder' => 400,
            'isActive' => ControllerHelper::isActivePath('admin', 'appearance', 'dark-mode'),
            'isVisible' => $canEditSettings,
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Accessibility'),
            'url' => ['/admin/appearance/accessibility'],
            'sortOrder' => 500,
            'isActive' => ControllerHelper::isActivePath('admin', 'appearance', 'accessibility'),
            'isVisible' => $canEditSettings,
        ]));

        $this->addEntry(new MenuLink([
            'label' => Yii::t('AdminModule.base', 'Custom SCSS'),
            'url' => ['/admin/appearance/custom-scss'],
            'sortOrder' => 600,
            'isActive' => ControllerHelper::isActivePath('admin', 'appearance', 'custom-scss'),
            'isVisible' => $canEditSettings,
        ]));

        parent::init();
    }
}
