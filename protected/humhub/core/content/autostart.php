<?php

\Yii::$app->moduleManager->register(array(
    'id' => 'content',
    'class' => \humhub\core\content\Module::className(),
    'isCoreModule' => true,
));
?>