<div class="panel panel-default animated fadeIn">

    <div class="install-header install-header-small"
         style="background-image: url('<?php echo $this->module->assetsUrl; ?>/humhub-install-header.jpg');">
        <h2 class="install-header-title"><?php echo Yii::t('InstallerModule.views_config_finished', '<strong>Setup</strong> Complete'); ?></h2>
    </div>

    <div class="panel-body text-center">
        <p class="lead"><?php echo Yii::t('InstallerModule.views_config_finished', "<strong>Congratulations</strong>. You're done."); ?></p>

        <p><?php echo Yii::t('InstallerModule.views_config_finished', "The installation completed successfully! Have fun with your new social network."); ?></p>

        <div class="text-center">
            <br/>
            <?php echo HHtml::link(Yii::t('InstallerModule.views_config_finished', 'Sign in'), Yii::app()->createUrl('/site/index'), array('class' => 'btn btn-primary')); ?>
            <br/><br/>
        </div>
    </div>
</div>
