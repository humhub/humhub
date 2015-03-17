<?php

// Only activate installer mode, when not installed yet or we are in console (translation files)
if (!Yii::app()->params['installed'] || Yii::app() instanceof CConsoleApplication) {
    Yii::app()->moduleManager->register(array(
        'id' => 'installer',
        'class' => 'application.modules_core.installer.InstallerModule',
        'isCoreModule' => true,
        'import' => array(
            'application.modules_core.installer.*',
        ),
    ));
}
?>