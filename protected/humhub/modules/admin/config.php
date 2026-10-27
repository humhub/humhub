<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\admin\Module;
use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\admin\Events;
use humhub\commands\CronController;
use humhub\modules\user\components\User;

return [
    'id' => 'admin',
    'class' => Module::class,
    'isCoreModule' => true,
    'urlManagerRules' => [
        ['pattern' => 'api/v2/module/<id:[\w\-]+>/enable', 'route' => 'admin/api/module/enable', 'verb' => 'POST'],
        ['pattern' => 'api/v2/admin/user', 'route' => 'admin/api/user/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/admin/group', 'route' => 'admin/api/group/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/admin/group/<id:\d+>/member', 'route' => 'admin/api/group-member/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/admin/group/<id:\d+>/member/bulk/remove', 'route' => 'admin/api/group-member/bulk-remove', 'verb' => 'POST'],
        ['pattern' => 'api/v2/admin/group/<id:\d+>/member/<userId:\d+>/<action:manager|member|remove>', 'route' => 'admin/api/group-member/<action>', 'verb' => 'POST'],
        ['pattern' => 'api/v2/admin/space', 'route' => 'admin/api/space/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/admin/invite', 'route' => 'admin/api/invite/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/admin/invite/bulk/<action:resend|delete>', 'route' => 'admin/api/invite/bulk', 'verb' => 'POST'],
        ['pattern' => 'api/v2/admin/invite/<id:\d+>/<action:resend|delete>', 'route' => 'admin/api/invite/<action>', 'verb' => 'POST'],
        ['pattern' => 'api/v2/admin/approval', 'route' => 'admin/api/approval/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/admin/approval/bulk/<action:approve|decline>', 'route' => 'admin/api/approval/bulk', 'verb' => 'POST'],
        ['pattern' => 'api/v2/admin/module', 'route' => 'admin/api/module/index', 'verb' => ['GET', 'HEAD']],
        ['pattern' => 'api/v2/admin/user/<id:\d+>/enable', 'route' => 'admin/api/user/enable', 'verb' => 'POST'],
        ['pattern' => 'api/v2/admin/user/<id:\d+>/disable', 'route' => 'admin/api/user/disable', 'verb' => 'POST'],
        ['pattern' => 'api/v2/admin/user/bulk/<action:enable|disable>', 'route' => 'admin/api/user/bulk', 'verb' => 'POST'],
    ],
    'events' => [
        [
            'class' => User::class,
            'event' => User::EVENT_BEFORE_SWITCH_IDENTITY,
            'callback' => [
                Events::class,
                'onSwitchUser',
            ],
        ],
        [
            'class' => Sidebar::class,
            'event' => Sidebar::EVENT_INIT,
            'callback' => [
                Events::class,
                'onDashboardSidebarInit',
            ],
        ],
        [
            'class' => CronController::class,
            'event' => CronController::EVENT_ON_DAILY_RUN,
            'callback' => [
                Events::class,
                'onCronDailyRun',
            ],
        ],
    ],
];
