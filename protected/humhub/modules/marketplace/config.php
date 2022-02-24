<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\commands\CronController;
use humhub\components\ModuleManager;
use humhub\modules\admin\widgets\ModuleControls;
use humhub\modules\admin\widgets\ModuleFilters;
use humhub\modules\admin\widgets\Modules;
use humhub\modules\marketplace\Events;
use humhub\modules\marketplace\Module;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'marketplace',
    'class' => Module::class,
    'isCoreModule' => true,
    'consoleControllerMap' => [
        'module' => 'humhub\modules\marketplace\commands\MarketplaceController',
        'professional-edition' => 'humhub\modules\marketplace\commands\ProfessionalEditionController'
    ],
    'events' => [
        [CronController::class, CronController::EVENT_ON_HOURLY_RUN, [Events::class, 'onHourlyCron']],
        [ModuleFilters::class, ModuleFilters::EVENT_INIT, [Events::class, 'onAdminModuleFiltersInit']],
        [ModuleFilters::class, ModuleFilters::EVENT_AFTER_RUN, [Events::class, 'onAdminModuleFiltersAfterRun']],
        [Modules::class, Modules::EVENT_INIT, [Events::class, 'onAdminModulesInit']],
        [ModuleManager::class, ModuleManager::EVENT_AFTER_FILTER_MODULES, [Events::class, 'onAdminModuleManagerAfterFilterModules']],
        [ModuleControls::class, ModuleControls::EVENT_INIT, [Events::class, 'onAdminModuleControlsInit']],
    ]
];
