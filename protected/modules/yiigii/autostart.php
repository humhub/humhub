<?php

Yii::app()->moduleManager->register(array(
    'id' => 'yiigii',
    'title' => Yii::t('YiiGiiModule.base', 'Yii - Gii Module'),
    'description' => Yii::t('YiiGiiModule.base', 'Includes Yiis Automatic Code Generator (Gii)'),
    'class' => 'application.modules.yiigii.YiiGiiModule',
    'import' => array(
        'application.modules.yiigii.*',
    ),
    'configRoute' => '//yiigii/config/index',
    // Events to Catch 
    'events' => array(
        array('class' => 'WebApplication', 'event' => 'onInit', 'callback' => array('YiiGiiModule', 'onWebApplicationInit')),
    ),
));
?>
