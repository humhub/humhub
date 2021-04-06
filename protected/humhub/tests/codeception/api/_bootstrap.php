<?php
$config = require(dirname(__DIR__) . '/config/api.php');
new humhub\components\Application($config);
