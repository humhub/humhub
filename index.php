<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

require(__DIR__ . '/protected/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env');
$dotenv->safeLoad();

$dynamicConfig = (is_readable(__DIR__ . '/protected/config/dynamic.php')) ? require(__DIR__ . '/protected/config/dynamic.php') : [];
$debug = filter_var($_ENV['HUMHUB_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN) || !filter_var($dynamicConfig['params']['installed'] ?? false, FILTER_VALIDATE_BOOLEAN);

defined('YII_DEBUG') or define('YII_DEBUG', $debug);
defined('YII_ENV') or define('YII_ENV', $debug ? 'dev' : 'prod');


require(__DIR__ . '/protected/vendor/yiisoft/yii2/Yii.php');


$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/protected/humhub/config/common.php'),
    require(__DIR__ . '/protected/humhub/config/web.php'),
    require(__DIR__ . '/protected/config/common.php'),
    require(__DIR__ . '/protected/config/web.php'),
    $dynamicConfig,
    humhub\helpers\EnvHelper::toConfig($_ENV, \humhub\components\Application::class),
);

try {
    (new humhub\components\Application($config))->run();
} catch (Throwable $e) {
    if (null === humhub\helpers\DatabaseHelper::handleConnectionErrors($e)) {
        throw $e;
    }
}
