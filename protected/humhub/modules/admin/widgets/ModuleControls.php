<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Module;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\Menu;
use Yii;

/**
 * Widget for rendering the context menu for module.
 */
class ModuleControls extends Menu
{

    /**
     * @var Module
     */
    public $module;

    /**
     * @inheritdoc
     */
    public $template = '@admin/widgets/views/moduleControls';

    /**
     * @var string|null
     */
    public $from;

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
        $this->initInstalledModuleControls();

        if ($marketplaceUrl = $this->getMarketplaceUrl($this->module)) {
            $this->addEntry(new MenuLink([
                'id' => 'marketplace-info',
                'label' => Yii::t('AdminModule.base', 'Information'),
                'url' => $marketplaceUrl,
                'htmlOptions' => ['rel' => 'noopener', 'target' => '_blank'],
                'icon' => 'external-link',
                'sortOrder' => 500,
            ]));
        } else {
            $this->addEntry(new MenuLink([
                'id' => 'info',
                'label' => Yii::t('AdminModule.base', 'Information'),
                'url' => ['/admin/module/info', 'moduleId' => $this->module->id],
                'htmlOptions' => ['data-target' => '#globalModal'],
                'icon' => 'info-circle',
                'sortOrder' => 600,
            ]));
        }
    }

    private function initInstalledModuleControls()
    {
        if (!($this->module instanceof Module)) {
            return;
        }

        if ($this->module->isActivated) {
            if ($this->module->getConfigUrl() != '') {
                $this->addEntry(new MenuLink([
                    'id' => 'configure',
                    'label' => Yii::t('AdminModule.base', 'Configure'),
                    'url' => $this->module->getConfigUrl(),
                    'icon' => 'wrench',
                    'sortOrder' => 100,
                ]));
            }

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
                'label' => Yii::t('AdminModule.base', 'Deactivate'),
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
                'label' => Yii::t('AdminModule.base', 'Activate'),
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
    }

    private function getMarketplaceUrl($module): ?string
    {
        if (!Yii::$app->hasModule('marketplace')) {
            return false;
        }

        static $onlineModules;

        if (!isset($modules)) {
            /* @var \humhub\modules\marketplace\Module $marketplaceModule */
            $marketplaceModule = Yii::$app->getModule('marketplace');
            $onlineModules = $marketplaceModule->onlineModuleManager->getModules();
        }

        return empty($onlineModules[$module->id]['marketplaceUrl'])
            ? null
            : $onlineModules[$module->id]['marketplaceUrl'];
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'class' => 'nav nav-pills preferences'
        ];
    }

    private function getActionUrl(string $url): array
    {
        $url = [$url, 'moduleId' => $this->module->id];

        if (!empty($this->from)) {
            $url['from'] = $this->from;
        }

        return $url;
    }

}
