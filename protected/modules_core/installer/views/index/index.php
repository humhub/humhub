<div class="panel panel-default animated fadeIn">

    <div class="install-header"
         style="background-image: url('<?php echo $this->module->assetsUrl; ?>/humhub-install-header.jpg');">
        <h2 class="install-header-title"><?php echo Yii::t('InstallerModule.views_index_index', '<strong>Setup</strong> Wizard'); ?></h2>
    </div>

    <div class="panel-body text-center">
        <br/>

        <p class="lead"><?php echo Yii::t('InstallerModule.views_index_index', '<strong>Welcome</strong> to HumHub<br>Your Social Network Toolbox'); ?></p>

        <p><?php echo Yii::t('InstallerModule.views_index_index', 'This wizard will install and configure your own HumHub instance.<br><br>To continue, click Next.'); ?></p>
        <br>
        <hr>
        <div class="row">
            <br/>
            <?php echo HHtml::link(Yii::t('InstallerModule.views_index_index', "Next") . ' <i class="fa fa-arrow-circle-right"></i>', array('go'), array('class' => 'btn btn-lg btn-primary')); ?>
            <br/>
            <br/>
        </div>
    </div>


</div>

<?php $this->widget('application.widgets.LanguageChooser'); ?>
