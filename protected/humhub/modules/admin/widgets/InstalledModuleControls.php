<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Module;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\marketplace\Module as MarketplaceModule;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\Menu;
use Yii;

/**
 * Widget for rendering the context menu for module.
 */
class InstalledModuleControls extends Menu
{
    public Module $module;

    /**
     * @inheritdoc
     */
    public $template = '@admin/widgets/views/installed-module-controls';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->initControls();
        parent::init();
    }

    public function initControls()
    {
        if ($this->module->getIsEnabled()) {
            if ($this->module instanceof ContentContainerModule) {
                $this->addEntry(new MenuLink([
                    'id' => 'default',
                    'label' => Yii::t('AdminModule.base', 'Set as default'),
                    'url' => $this->getActionUrl('/admin/module/set-as-default'),
                    'htmlOptions' => ['data-target' => '#globalModal'],
                    'icon' => 'check-square',
                    'sortOrder' => 200,
                ]));
            }

            $this->addEntry(new MenuLink([
                'id' => 'deactivate',
                'label' => Yii::t('AdminModule.base', 'Disable'),
                'url' => $this->getActionUrl('/admin/module/disable'),
                'htmlOptions' => [
                    'data-method' => 'POST',
                    'data-confirm' => Yii::t('AdminModule.modules', 'Are you sure? *ALL* module data will be lost!'),
                ],
                'icon' => 'minus-circle',
                'sortOrder' => 300,
            ]));
        } else {
            $this->addEntry(new MenuLink([
                'id' => 'deactivate',
                'label' => Yii::t('AdminModule.base', 'Enable'),
                'url' => $this->getActionUrl('/admin/module/enable'),
                'htmlOptions' => [
                    'data-method' => 'POST',
                    'data-loader' => 'modal',
                    'data-message' => Yii::t('AdminModule.modules', 'Enable module...'),
                ],
                'icon' => 'check-circle',
                'sortOrder' => 300,
            ]));
        }

        if (Yii::$app->moduleManager->canRemoveModule($this->module->id)) {
            $this->addEntry(new MenuLink([
                'id' => 'uninstall',
                'label' => Yii::t('AdminModule.base', 'Uninstall'),
                'url' => $this->getActionUrl('/admin/module/remove'),
                'htmlOptions' => [
                    'data-method' => 'POST',
                    'data-confirm' => Yii::t('AdminModule.modules', 'Are you sure? *ALL* module related data and files will be lost!'),
                ],
                'icon' => 'trash',
                'sortOrder' => 400,
            ]));
        }

        /** @var \humhub\modules\marketplace\Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');

        if (
            MarketplaceModule::isMarketplaceEnabled()
            && dirname($this->module->basePath) === Yii::getAlias($marketplaceModule->modulesPath)
        ) {
            $this->addEntry(new MenuLink([
                'id' => 'info',
                'label' => Yii::t('AdminModule.base', 'Show in Marketplace'),
                'url' => ['/marketplace/browse', 'id' => $this->module->id],
                'icon' => 'info-circle',
                'sortOrder' => 600,
            ]));
        }
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'nav nav-pills preferences',
        ];
    }

    private function getActionUrl(string $url): array
    {
        return [$url, 'moduleId' => $this->module->id];
    }

    /**
     * @inerhitdoc
     */
    public function beforeRun()
    {
        if (!Yii::$app->user->can(ManageModules::class)) {
            return false;
        }
        return parent::beforeRun();
    }
}
