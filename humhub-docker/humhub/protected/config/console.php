<?php

/**
 * This file provides to overwrite the default HumHub / Yii configuration by your local Console environments
 * @see http://www.yiiframework.com/doc-2.0/guide-concept-configurations.html
 * @see http://docs.humhub.org/admin-installation-configuration.html
 * @see http://docs.humhub.org/dev-environment.html
 */

return [
    'controllerMap' => [
        'installer' => 'humhub\modules\installer\commands\InstallController'
    ],
    'components' => [
        'urlManager' => [
            'baseUrl' => 'http://localhost:80',
            'hostInfo' => 'http://localhost:80',
        ]
    ]
];
