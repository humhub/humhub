<?php

Yii::$app->moduleManager->register([
    'id' => 'installer',
    'class' => humhub\core\installer\Module::className(),
    'isCoreModule' => true,
]);
?>