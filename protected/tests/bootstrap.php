<?php

// change the following paths if necessary
$yiit=dirname(__FILE__).'/../vendors/yii/yiit.php';
$config=dirname(__FILE__).'/../config/test.php';

require_once($yiit);

$appClass = dirname(__FILE__) . '/../components/WebApplication.php';
require_once($appClass);

Yii::createApplication('WebApplication', $config);

// Initially load fixture manager, to make sure test database is created.
Yii::app()->fixture;

Yii::app()->user->id = 1;
     

