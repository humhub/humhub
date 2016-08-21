<?php
/**
 * Initialize the HumHub Application for functional testing. The default application configuration for this suite can be overwritten
 * in @tests/config/functional.php
 */
$config = require(Yii::getAlias('@tests/config/functional.php'));

new humhub\components\Application($config);

$cfg = \Codeception\Configuration::config();

if(!empty($cfg['humhub_modules'])) {
    Yii::$app->moduleManager->enableModules($cfg['humhub_modules']);
}