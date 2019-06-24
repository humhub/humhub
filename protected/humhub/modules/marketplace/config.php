<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\marketplace\Events;
use humhub\modules\admin\widgets\ModuleMenu;
use humhub\modules\marketplace\Module;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'marketplace',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        ['humhub\components\console\Application', 'onInit', [Events::class, 'onConsoleApplicationInit']],
        [ModuleMenu::class, ModuleMenu::EVENT_INIT, [Events::class, 'onAdminModuleMenuInit']]
    ]
];
