<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\commands\CronController;
use humhub\components\ModuleManager;
use humhub\modules\marketplace\Events;
use humhub\modules\marketplace\Module;
use humhub\modules\user\widgets\AccountTopMenu;
use humhub\widgets\MetaSearchWidget;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'marketplace',
    'class' => Module::class,
    'isCoreModule' => true,
    'consoleControllerMap' => [
        'module' => 'humhub\modules\marketplace\commands\MarketplaceController',
        'professional-edition' => 'humhub\modules\marketplace\commands\ProfessionalEditionController',
    ],
    'events' => [
        [CronController::class, CronController::EVENT_ON_HOURLY_RUN, [Events::class, 'onHourlyCron']],
        [ModuleManager::class, ModuleManager::EVENT_AFTER_FILTER_MODULES, [Events::class, 'onMarketplaceAfterFilterModules']],
        [AccountTopMenu::class, AccountTopMenu::EVENT_INIT, [Events::class, 'onAccountTopMenuInit']],
        [MetaSearchWidget::class, MetaSearchWidget::EVENT_INIT, [Events::class, 'onMetaSearchInit']],
    ],
];
