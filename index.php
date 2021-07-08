<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

if ((isset($_SERVER['HTTP_SEC_CH_UA']) && strpos($_SERVER['HTTP_SEC_CH_UA'], 'Chromium') !== false)) {
    // Use special index page with test config when it is run by Chromium driver for acceptance tests
    require(__DIR__ . '/index-test.php');
    exit;
}

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

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
