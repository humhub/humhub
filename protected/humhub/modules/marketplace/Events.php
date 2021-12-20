<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace;

use humhub\components\ModuleManager;
use humhub\modules\admin\widgets\ModuleFilters;
use humhub\modules\admin\widgets\Modules;
use humhub\modules\marketplace\models\Module as ModelModule;
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
                'title' => Yii::t('AdminModule.base', 'Categories'),
                'type' => 'dropdown',
                'options' => $categories,
                'wrapperClass' => 'col-md-3',
                'sortOrder' => 200,
            ]);
        }
    }

    public static function onAdminModulesInit($event)
    {
        /* @var Modules $modulesWidget */
        $modulesWidget = $event->sender;

        /* @var Module $marketplaceModule */
        $marketplaceModule = Yii::$app->getModule('marketplace');
        $onlineModules = $marketplaceModule->onlineModuleManager->getModules();

        foreach ($onlineModules as $o => $onlineModule) {
            $onlineModule = new ModelModule($onlineModule);
            if ($onlineModule->isInstalled()) {
                unset($onlineModules[$o]);
                continue;
            }
            $onlineModules[$o] = $onlineModule;
        }

        if (empty($onlineModules)) {
            return;
        }

        $modulesWidget->addGroup('notInstalled', [
            'title' => Yii::t('AdminModule.modules', 'Not Installed'),
            'modules' => Yii::$app->moduleManager->filterModules($onlineModules, Yii::$app->request->get('keyword')),
            'count' => count($onlineModules),
            'view' => '@humhub/modules/marketplace/widgets/views/moduleCard',
        ]);
    }

    public static function onAdminModuleManagerAfterFilterModules($event)
    {
        /* @var ModuleManager $moduleManager */
        $moduleManager = $event->sender;

        // TODO: Filter by categories and tags
    }

}
