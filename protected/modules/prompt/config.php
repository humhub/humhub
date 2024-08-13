<?php

use app\humhub\modules\prompt\Events;
use humhub\modules\admin\widgets\AdminMenu;
use humhub\widgets\TopMenu;

return [
	'id' => 'prompt',
	'class' => 'app\humhub\modules\prompt\Module',
	'namespace' => 'app\humhub\modules\prompt',
	'events' => [
		[
			'class' => TopMenu::class,
			'event' => TopMenu::EVENT_INIT,
			'callback' => [Events::class, 'onTopMenuInit'],
		],
		[
			'class' => AdminMenu::class,
			'event' => AdminMenu::EVENT_INIT,
			'callback' => [Events::class, 'onAdminMenuInit']
		],
		[
			'class' => 'humhub\modules\rest\Module',
			'event' => 'restApiAddRules',
			'callback' => [Events::class, 'onRestApiAddRules'],
		],
	],
];
