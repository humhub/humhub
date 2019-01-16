<?php
/**
 * This file provides to overwrite the default HumHub / Yii configuration by your local common (Console and Web) environments
 * @see http://www.yiiframework.com/doc-2.0/guide-concept-configurations.html
 * @see http://docs.humhub.org/admin-installation-configuration.html
 * @see http://docs.humhub.org/dev-environment.html
 */
return [
    'params' => [
        'passwordAdditionalRules' => [],
    ],
    'modules' => [
        'user' => [
            'passwordStrength' => [
                '/^(.*?[A-Z]){2,}.*$/' => 'Password has to contain two uppercase letters.',
                '/^.{8,}$/' => 'Password needs to be at least 8 characters long.',
            ]
        ]
    ]
];
