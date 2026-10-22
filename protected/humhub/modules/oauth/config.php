<?php

use humhub\commands\CronController;
use humhub\components\Controller;
use humhub\components\gates\GateManager;
use humhub\modules\oauth\commands\OauthController;
use humhub\modules\oauth\Events;
use humhub\modules\oauth\Module;
use humhub\modules\user\models\Password;
use humhub\modules\user\widgets\SecuritySettingsMenu;
use yii\db\ActiveRecord;
use yii\web\User;

return [
    'id' => 'oauth',
    'class' => Module::class,
    'isCoreModule' => true,
    'consoleControllerMap' => [
        'oauth' => OauthController::class,
    ],
    'urlManagerRules' => [
        ['pattern' => 'oauth/authorize', 'route' => 'oauth/authorize/index', 'verb' => ['GET', 'POST']],
        ['pattern' => 'oauth/token', 'route' => 'oauth/token/token', 'verb' => 'POST'],
        ['pattern' => 'oauth/revoke', 'route' => 'oauth/token/revoke', 'verb' => 'POST'],
        ['pattern' => 'api/v2/oauth/web-session', 'route' => 'oauth/api/web-session/create', 'verb' => 'POST'],
        ['pattern' => 'oauth/web-session', 'route' => 'oauth/web-session/index', 'verb' => ['GET']],
        ['pattern' => '.well-known/oauth-authorization-server', 'route' => 'oauth/metadata/index', 'verb' => ['GET', 'HEAD']],
    ],
    'events' => [
        [CronController::class, CronController::EVENT_ON_DAILY_RUN, [Events::class, 'onCronDailyRun']],
        [GateManager::class, GateManager::EVENT_INIT_GATES, [Events::class, 'onGateInit']],
        [Controller::class, Controller::EVENT_BEFORE_ACTION, [Events::class, 'onBeforeAction']],
        [User::class, User::EVENT_BEFORE_LOGOUT, [Events::class, 'onUserBeforeLogout']],
        [User::class, User::EVENT_AFTER_LOGIN, [Events::class, 'onUserAfterLogin']],
        [Password::class, ActiveRecord::EVENT_AFTER_INSERT, [Events::class, 'onPasswordAfterInsert']],
        [Password::class, ActiveRecord::EVENT_AFTER_UPDATE, [Events::class, 'onPasswordAfterUpdate']],
        [SecuritySettingsMenu::class, SecuritySettingsMenu::EVENT_INIT, [Events::class, 'onSecuritySettingsMenuInit']],
    ],
];
