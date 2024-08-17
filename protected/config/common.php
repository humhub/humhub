<?php
/**
 * This file provides to overwrite the default HumHub / Yii configuration by your local common (Console and Web) environments
 * @see http://www.yiiframework.com/doc-2.0/guide-concept-configurations.html
 * @see https://docs.humhub.org/docs/admin/advanced-configuration
 */

$common = [];

if (!empty($moduleAutoloadPaths = $_ENV['moduleAutoloadPaths'])) {
    $moduleAutoloadPaths = explode(',', $moduleAutoloadPaths);

    $common['params']['moduleAutoloadPaths'] = $moduleAutoloadPaths;
}

return [
    'params' => [
        'moduleAutoloadPaths' => [
            '/app/modules/humhub',
            '/app/modules/humhub-contrib',
        ],
    ],
];
