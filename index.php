<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

$commonConfig = require(__DIR__ . '/protected/config/common.php');

defined('YII_DEBUG') or define('YII_DEBUG', DEBUG);
defined('YII_ENV') or define('YII_ENV', DEBUG ? 'dev' : 'prod');

require(__DIR__ . '/protected/vendor/autoload.php');
require(__DIR__ . '/protected/vendor/yiisoft/yii2/Yii.php');


$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/protected/humhub/config/common.php'),
    require(__DIR__ . '/protected/humhub/config/web.php'),
    (is_readable(__DIR__ . '/protected/config/dynamic.php')) ? require(__DIR__ . '/protected/config/dynamic.php') : [],
    $commonConfig,
    require(__DIR__ . '/protected/config/web.php')
);

try {
    (new humhub\components\Application($config))->run();
} catch (\Throwable $ex) {
    if (null === humhub\helpers\DatabaseHelper::handleConnectionErrors($ex)) {
        throw $ex;
    }
}
