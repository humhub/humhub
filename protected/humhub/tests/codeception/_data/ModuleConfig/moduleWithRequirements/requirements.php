<?php

if (Yii::$app->getModule('module1') === null) {
    return 'This module cannot work without enabled module "module1"';
}

return null;
