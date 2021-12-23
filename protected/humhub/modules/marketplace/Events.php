<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace;

use humhub\components\Module as CoreModule;
use humhub\modules\admin\events\ModulesEvent;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\admin\widgets\ModuleControls;
use humhub\modules\admin\widgets\ModuleFilters;
use humhub\modules\admin\widgets\Modules;
use humhub\modules\marketplace\models\Module as ModelModule;
use humhub\modules\ui\menu\MenuLink;
use humhub\widgets\Button;
use Yii;
use yii\base\BaseObject;
use yii\base\Event;
use yii\helpers\Url;

class Events extends BaseObject
{

    /**
     * On console application initialization
     *
     * @param Event $event
     */
    public static function onConsoleApplicationInit($event)
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('marketplace');

        if (!$module->enabled) {
            return;
        }

        $application = $event->sender;
        $application->controllerMap['module'] = commands\MarketplaceController::class;
    }

    public static function onAdminModuleMenuInit($events)
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('marketplace');

        if (!$module->enabled) {
            return;
        }

        $updatesBadge = '';
        $updatesCount = count($module->onlineModuleManager->getModuleUpdates());
        if ($updatesCount > 0) {
            $updatesBadge = '&nbsp;&nbsp;<span class="label label-danger">' . $updatesCount . '</span>';
        } else {
            $updatesBadge = '&nbsp;&nbsp;<span class="label label-default">0</span>';
        }

        $events->sender->addItem([
            'label' => Yii::t('MarketplaceModule.base', 'Browse online'),
            'url' => Url::to(['/marketplace/browse']),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->id == 'browse'),
        ]);

        $events->sender->addItem([
            'label' => Yii::t('MarketplaceModule.base', 'Purchases'),
            'url' => Url::to(['/marketplace/purchase']),
            'sortOrder' => 300,
            'isActive' => (Yii::$app->controller->id == 'purchase'),
        ]);

        $events->sender->addItem([
            'label' => Yii::t('MarketplaceModule.base', 'Available updates') . $updatesBadge,
            'url' => Url::to(['/marketplace/update']),
            'sortOrder' => 400,
            'isActive' => (Yii::$app->controller->id == 'update'),
        ]);

    }

    public static function onHourlyCron($event)
    {
        Yii::$app->queue->push(new jobs\PeActiveCheckJob());
        Yii::$app->queue->push(new jobs\ModuleCleanupsJob());
    }

    public static function onAdminModuleFiltersInit($event)
    {
        /* @var ModuleFilters $moduleFilters */
        $moduleFilters = $event->sender;

        /* @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        $marketplaceModule->onlineModuleManager->getModules();
        $categories = $marketplaceModule->onlineModuleManager->getCategories();
        if (!empty($categories)) {
            $moduleFilters->addFilter('categoryId', [
                'title' => Yii::t('MarketplaceModule.base', 'Categories'),
                'type' => 'dropdown',
                'options' => $categories,
                'wrapperClass' => 'col-md-3',
                'sortOrder' => 200,
            ]);
        }

        $moduleFilters->addFilter('tags', [
            'title' => Yii::t('MarketplaceModule.base', 'Tags'),
            'type' => 'tags',
            'tags' => [
                '' => Yii::t('MarketplaceModule.base', 'All'),
                'installed' => Yii::t('MarketplaceModule.base', 'Installed'),
                'not_installed' => Yii::t('MarketplaceModule.base', 'Not Installed'),
                'professional' => Yii::t('MarketplaceModule.base', 'Professional Edition'),
                'featured' => Yii::t('MarketplaceModule.base', 'Featured'),
                'official' => Yii::t('MarketplaceModule.base', 'Official'),
                'partner' => Yii::t('MarketplaceModule.base', 'Partner'),
                'new' => Yii::t('MarketplaceModule.base', 'New'),
            ],
            'wrapperClass' => 'col-md-12 form-search-filter-tags',
            'sortOrder' => 20000,
        ]);
    }

    public static function onAdminModuleFiltersAfterRun($event)
    {
        $latestVersion = HumHubAPI::getLatestHumHubVersion();
        if (!$latestVersion) {
            return;
        }

        if (version_compare($latestVersion, Yii::$app->version, '>')) {
            $info = [
                'class' => 'directory-filters-footer-warning',
                'icon' => 'info-circle',
                'info' => Yii::t('MarketplaceModule.base', 'A new HumHub update is available. Install it now to keep your network up to date and to have access to the latest module versions.'),
                'link' => Button::asLink(Yii::t('MarketplaceModule.base', 'Update HumHub now'), 'https://www.humhub.org')
                    ->cssClass('btn btn-primary'),
            ];
        } else {
            $info = [
                'class' => 'directory-filters-footer-info',
                'icon' => 'check-circle',
                'info' => Yii::t('MarketplaceModule.base', 'This HumHub installation is up to date!'),
                'link' => Button::asLink('https://www.humhub.org', 'https://www.humhub.org')
                    ->cssClass('btn btn-info'),
            ];
        }

        /* @var ModuleFilters $moduleFilters */
        $moduleFilters = $event->sender;
        $event->result .= $moduleFilters->render('@humhub/modules/marketplace/widgets/views/moduleUpdateInfo', $info);
    }

    public static function onAdminModulesInit($event)
    {
        /* @var Modules $modulesWidget */
        $modulesWidget = $event->sender;

        /* @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');

        $updateModules = $marketplaceModule->onlineModuleManager->getAvailableUpdateModules();
        if ($updateModulesCount = count($updateModules)) {
            $modulesWidget->addGroup('availableUpdates', [
                'title' => Yii::t('AdminModule.modules', 'Available Updates'),
                'modules' => $updateModules,
                'count' => $updateModulesCount,
                'view' => '@humhub/modules/marketplace/widgets/views/moduleUpdateCard',
                'groupTemplate' => '<div class="container-module-updates">{group}</div>',
                'moduleTemplate' => '<div class="card card-module col-lg-2 col-md-3 col-sm-4 col-xs-6">{card}</div>',
                'sortOrder' => 10,
            ]);
        }

        $onlineModules = $marketplaceModule->onlineModuleManager->getNotInstalledModules();
        if ($onlineModulesCount = count($onlineModules)) {
            $modulesWidget->addGroup('notInstalled', [
                'title' => Yii::t('AdminModule.modules', 'Not Installed'),
                'modules' => Yii::$app->moduleManager->filterModules($onlineModules),
                'count' => $onlineModulesCount,
                'view' => '@humhub/modules/marketplace/widgets/views/moduleInstallCard',
                'sortOrder' => 200,
            ]);
        }
    }

    public static function onAdminModuleManagerAfterFilterModules(ModulesEvent $event)
    {
        if (!is_array($event->modules)) {
            return;
        }

        foreach ($event->modules as $m => $module) {
            if (!self::isFilteredModule($module)) {
                unset($event->modules[$m]);
            }
        }
    }

    /**
     * @param CoreModule|ModelModule $module
     * @return bool
     */
    private static function isFilteredModule($module): bool
    {
        return self::isFilteredModuleByCategory($module) &&
            self::isFilteredModuleByTags($module);
    }

    /**
     * @param CoreModule|ModelModule $module
     * @return bool
     */
    private static function isFilteredModuleByCategory($module): bool
    {
        $categoryId = Yii::$app->request->get('categoryId', null);

        if (empty($categoryId)) {
            return true;
        }

        if (!is_array($module->categories) || empty($module->categories)) {
            return false;
        }

        return in_array($categoryId, $module->categories);
    }

    /**
     * @param CoreModule|ModelModule $module
     * @return bool
     */
    private static function isFilteredModuleByTags($module): bool
    {
        $tags = Yii::$app->request->get('tags', null);

        if (empty($tags)) {
            return true;
        }

        $tags = explode(',', $tags);

        foreach ($tags as $tag) {
            switch ($tag) {
                case 'installed':
                    if (!Yii::$app->moduleManager->hasModule($module->id)) {
                        return false;
                    }
                    break;
                case 'not_installed':
                    if (Yii::$app->moduleManager->hasModule($module->id)) {
                        return false;
                    }
                    break;
                case 'professional':
                    if (!$module->isProOnly()) {
                        return false;
                    }
                    break;
                case 'featured':
                    if (!$module->getOnlineInfo('featured')) {
                        return false;
                    }
                    break;
                case 'official':
                    if (!$module->getOnlineInfo('isCommunity')) {
                        return false;
                    }
                    break;
                case 'partner':
                    if (!$module->getOnlineInfo('isThirdParty')) {
                        return false;
                    }
                    break;
                case 'new':
                    // TODO: Filter by new status
                    break;
            }
        }

        return true;
    }

    public static function onAdminModuleControlsInit($event)
    {
        /* @var ModuleControls $moduleControls */
        $moduleControls = $event->sender;

        if (!($moduleControls->module instanceof ModelModule)) {
            return;
        }

        if ($moduleControls->module->isThirdParty) {
            $moduleControls->addEntry(new MenuLink([
                'id' => 'marketplace-third-party',
                'label' => Yii::t('MarketplaceModule.base', 'Third-party')
                    . ($moduleControls->module->isCommunity ? ' - ' . Yii::t('MarketplaceModule.base', 'Community') : ''),
                'url' => ['/marketplace/browse/thirdparty-disclaimer'],
                'htmlOptions' => ['data-target' => '#globalModal'],
                'icon' => 'info-circle',
                'sortOrder' => 1000,
            ]));
        }
    }
}
