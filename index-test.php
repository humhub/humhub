<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

// NOTE: Make sure this file is not accessible when deployed to production
use humhub\components\Response;

if (!in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('You are not allowed to access this file.');
}

if (isset($_SERVER['REQUEST_URI']) && preg_match('/^[^?]*\.(?:css|(?<!manifest\.)json|(?<!sw\.)js|png|jpg|jpeg|gif|ttf|woff|woff2)(\?.+)?$/i', $_SERVER['REQUEST_URI'])) {
    return false; // serve the requested resource as-is.
}

file_put_contents("php://stdout", sprintf("[%s] \e[34m%s:%d [---]: %s %s\033[0m\n", date('D M d H:i:s Y'), $_SERVER['REMOTE_ADDR'], $_SERVER['REMOTE_PORT'], $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']));

/*defined('YII_DEBUG') or define('YII_DEBUG', true);*/
defined('YII_ENV') or define('YII_ENV', 'test');
defined('YII_ENV_TEST') or define('YII_ENV_TEST', true);


require(__DIR__ . '/protected/vendor/autoload.php');
require(__DIR__ . '/protected/vendor/yiisoft/yii2/Yii.php');

$config = yii\helpers\ArrayHelper::merge(
    // add more configurations here
    (is_readable(__DIR__ . '/protected/humhub/tests/codeception/config/dynamic.php')) ? require(__DIR__ . '/protected/humhub/tests/codeception/config/dynamic.php') : [],
    require(__DIR__ . '/protected/humhub/tests/codeception/config/acceptance.php')
);

require_once './protected/vendor/codeception/codeception/autoload.php';

include './protected/humhub/tests/c3.php';

class indexTextResponseCode
{
    public static ?Response $response = null;
}

(new humhub\components\Application($config))->run();

if (indexTextResponseCode::$response) {
    $code = indexTextResponseCode::$response->getStatusCode();
    switch (floor($code / 100)) {
        case 2:
            $color = 34;
            break;

        case 1:
        case 3:
            $color = 36;
            break;

        case 4:
            $color = 33;
            break;

        default:
            $color = 31;
    }
    file_put_contents("php://stdout", sprintf("[%s] \e[%dm%s:%d [%03d]: %s %s\033[0m\n", date('D M d H:i:s Y'), $color, $_SERVER['REMOTE_ADDR'], $_SERVER['REMOTE_PORT'], $code, $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']));
}
