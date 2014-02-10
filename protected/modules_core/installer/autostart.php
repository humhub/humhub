<?php

// Only activate installer mode, when not installed yet
if (!Yii::app()->params['installed']) {
    Yii::app()->moduleManager->register(array(
        'id' => 'installer',
        'title' => Yii::t('InstallerModule.base', 'Installer'),
        'description' => Yii::t('InstallerModule.base', 'Initial Installer.'),
        'class' => 'application.modules_core.installer.InstallerModule',
        'isCoreModule' => true,
    ));
}
?>