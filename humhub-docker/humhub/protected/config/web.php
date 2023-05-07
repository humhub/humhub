<?php

/**
 * This file provides to overwrite the default HumHub / Yii configuration by your local Web environments
 * @see http://www.yiiframework.com/doc-2.0/guide-concept-configurations.html
 * @see http://docs.humhub.org/admin-installation-configuration.html
 * @see http://docs.humhub.org/dev-environment.html
 */

return [
    'components' => [
        'request' => [
            'trustedHosts' => ['127.0.0.1/32']
        ],
    ]
];
