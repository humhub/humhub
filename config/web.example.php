<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * Local configuration of the web application.
 *
 * Copy this file to `web.php` in the same directory to activate it. It is merged on top of
 * `common.php`, so only put things here that must not apply to console runs.
 *
 * @see https://docs.humhub.org/docs/admin/advanced-configuration
 * @see https://www.yiiframework.com/doc/guide/2.0/en/concept-configurations
 */
return [
    // 'components' => [
    //     // Behind a reverse proxy or load balancer: trust the `X-Forwarded-*` headers of the
    //     // proxy, so HumHub sees the visitor's address and the outside scheme instead of the
    //     // proxy's. List the proxy addresses, never `0.0.0.0/0` - any client could then claim
    //     // any address.
    //     'request' => [
    //         'trustedHosts' => ['10.0.0.0/8'],
    //     ],
    // ],
    //
    // // Yii debug toolbar, for request and query profiling on a development installation.
    // 'bootstrap' => ['debug'],
    // 'modules' => [
    //     'debug' => [
    //         'class' => \yii\debug\Module::class,
    //         'allowedIPs' => ['127.0.0.1', '::1'],
    //     ],
    // ],
];
