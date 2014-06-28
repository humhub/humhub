<?php

// Only activate installer mode, when not installed yet
if (!Yii::app()->params['installed']) {
    Yii::app()->moduleManager->register(array(
        'id' => 'installer',
        'class' => 'application.modules_core.installer.InstallerModule',
        'isCoreModule' => true,
    ));
}
?>