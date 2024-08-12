<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

// comment out the following line to enable debug mode
//defined('DEBUG') or define('DEBUG', true);


$dynamicConfig = (is_readable(__DIR__ . '/protected/config/dynamic.php')) ? require(__DIR__ . '/protected/config/dynamic.php') : [];
$debug = (defined('DEBUG') && DEBUG) || !$dynamicConfig['params']['installed'];

defined('YII_DEBUG') or define('YII_DEBUG', $debug);
defined('YII_ENV') or define('YII_ENV', $debug ? 'dev' : 'prod');

require(__DIR__ . '/protected/vendor/autoload.php');
require(__DIR__ . '/protected/vendor/yiisoft/yii2/Yii.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/protected/humhub/config/common.php'),
    require(__DIR__ . '/protected/humhub/config/web.php'),
    require(__DIR__ . '/protected/config/common.php'),
    require(__DIR__ . '/protected/config/web.php'),
    $dynamicConfig,
);

try {
    (new humhub\components\Application($config))->run();
} catch (\Throwable $ex) {
    if (null === humhub\helpers\DatabaseHelper::handleConnectionErrors($ex)) {
        throw $ex;
    }
}
