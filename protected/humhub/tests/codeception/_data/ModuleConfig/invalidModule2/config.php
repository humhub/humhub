<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

$config = require dirname(__DIR__) . "/module1/config.php";
$config['id'] = basename(__DIR__);
$config['class'] = \yii\base\Module::class;

return $config;
