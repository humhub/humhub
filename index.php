<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

// IPs to access HumHub on DEBUG/DEV mode
$debugmode = [
    '127.0.0.1',
    '::1',
    //'YOUR_IP_HERE'
];

// DEBUG and DEV are ON as defaut when accessing HumHub from local IPs
// Add your public IPs to the '$debugmode' array if you need to enable it on remote hosts
if (in_array(@$_SERVER['REMOTE_ADDR'], $debugmode)) {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_ENV') or define('YII_ENV', 'dev');
}

require(__DIR__ . '/protected/vendor/autoload.php');
require(__DIR__ . '/protected/vendor/yiisoft/yii2/Yii.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/protected/humhub/config/common.php'),
    require(__DIR__ . '/protected/humhub/config/web.php'),
    (is_readable(__DIR__ . '/protected/config/dynamic.php')) ? require(__DIR__ . '/protected/config/dynamic.php') : [],
    require(__DIR__ . '/protected/config/common.php'),
    require(__DIR__ . '/protected/config/web.php')
);

(new humhub\components\Application($config))->run();
