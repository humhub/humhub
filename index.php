<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/protected/vendor/autoload.php');
require(__DIR__ . '/protected/vendor/yiisoft/yii2/Yii.php');
$config = require(__DIR__ . '/protected/config/web.php');

Yii::setAlias('@webroot', __DIR__);
Yii::setAlias('@app', __DIR__ . DIRECTORY_SEPARATOR . 'protected');
Yii::setAlias('@humhub', '@app/humhub');

(new yii\web\Application($config))->run();
