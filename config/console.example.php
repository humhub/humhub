<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * Local configuration of the console application - `protected/yii`, the cron jobs and the queue
 * worker.
 *
 * Copy this file to `console.php` in the same directory to activate it. It is merged on top of
 * `common.php`, so only put things here that must not apply to web requests.
 *
 * The base URL of links generated on the console is not configured here: it comes from the
 * `baseUrl` setting under Administration -> Settings.
 *
 * @see https://docs.humhub.org/docs/admin/advanced-configuration
 * @see https://www.yiiframework.com/doc/guide/2.0/en/concept-configurations
 */
return [
    // 'components' => [
    //     // Log cron and queue runs into their own file instead of the one the web application
    //     // writes. Targets are keyed by class, so this configures the shipped one rather than
    //     // adding a second.
    //     'log' => [
    //         'targets' => [
    //             \yii\log\FileTarget::class => [
    //                 'logFile' => '@runtime/logs/console.log',
    //             ],
    //         ],
    //     ],
    // ],
];
