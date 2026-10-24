<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\commands\CronController;
use humhub\modules\marketplace\Events;
use humhub\modules\marketplace\Module;
use humhub\modules\user\widgets\AccountTopMenu;
use humhub\widgets\MetaSearchWidget;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'marketplace',
    'class' => Module::class,
    'isCoreModule' => true,
    'urlManagerRules' => [
        ['pattern' => 'api/v2/marketplace/use-case', 'route' => 'marketplace/api/marketplace/use-case', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/marketplace/module', 'route' => 'marketplace/api/module/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/marketplace/module/<id:[\w\-]+>/install', 'route' => 'marketplace/api/module/install', 'verb' => 'POST'],
        ['pattern' => 'api/v2/marketplace/module/<id:[\w\-]+>/update', 'route' => 'marketplace/api/module/update', 'verb' => 'POST'],
        ['pattern' => 'api/v2/marketplace/category', 'route' => 'marketplace/api/marketplace/categories', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/marketplace/core-version', 'route' => 'marketplace/api/marketplace/core-version', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/marketplace/settings', 'route' => 'marketplace/api/marketplace/settings', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/marketplace/settings', 'route' => 'marketplace/api/marketplace/update-settings', 'verb' => 'PATCH'],
        ['pattern' => 'api/v2/marketplace/licence-key', 'route' => 'marketplace/api/marketplace/licence-key', 'verb' => 'POST'],
    ],
    'consoleControllerMap' => [
        'module' => 'humhub\modules\marketplace\commands\MarketplaceController',
        'professional-edition' => 'humhub\modules\marketplace\commands\ProfessionalEditionController',
    ],
    'events' => [
        [CronController::class, CronController::EVENT_ON_HOURLY_RUN, [Events::class, 'onHourlyCron']],
        [AccountTopMenu::class, AccountTopMenu::EVENT_INIT, [Events::class, 'onAccountTopMenuInit']],
        [MetaSearchWidget::class, MetaSearchWidget::EVENT_INIT, [Events::class, 'onMetaSearchInit']],
    ],
];
