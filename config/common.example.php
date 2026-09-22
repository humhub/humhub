<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * Local configuration of the web application and the console.
 *
 * Copy this file to `common.php` in the same directory to activate it. What you return here
 * overrides the HumHub defaults; `web.php` and `console.php` beside it override this file for the
 * respective application, and the `.env` file in the installation root overrides all three.
 *
 * @see https://docs.humhub.org/docs/admin/advanced-configuration
 * @see https://www.yiiframework.com/doc/guide/2.0/en/concept-configurations
 */
return [
    // 'components' => [
    //     // URL rewriting: serve URLs without `index.php` in them. The shipped `public/.htaccess`
    //     // already does the rewriting for Apache; on nginx the `try_files` rule takes care of it.
    //     'urlManager' => [
    //         'enablePrettyUrl' => true,
    //         'showScriptName' => false,
    //     ],
    //
    //     // Replace single views or whole view directories without writing a theme. Paths are
    //     // Yii aliases, and `@config` is this directory.
    //     'view' => [
    //         'theme' => [
    //             'pathMap' => [
    //                 // per-file override (the key has to end in `.php`)
    //                 '@humhub/modules/user/views/auth/login.php' => '@config/views/login.php',
    //
    //                 // directory override
    //                 '@humhub/modules/space/widgets/views' => '@config/views/space-widgets',
    //             ],
    //         ],
    //     ],
    // ],
    //
    // 'params' => [
    //     // Load modules from additional directories, for instance to keep your own modules out
    //     // of the directory the marketplace installs into.
    //     'moduleAutoloadPaths' => ['@root/custom-modules', '@app/modules', '@humhub/modules'],
    // ],
];
