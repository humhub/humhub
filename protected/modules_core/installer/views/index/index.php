<div class="panel panel-default animated fadeIn">

    <div class="install-header"
         style="background-image: url('<?php echo $this->module->assetsUrl; ?>/humhub-install-header.jpg');">
        <h2 class="install-header-title"><?php echo Yii::t('InstallerModule.base', '<strong>Setup</strong> Wizard'); ?></h2>
    </div>

    <div class="panel-body text-center">
        <br/>

        <p class="lead"><?php echo Yii::t('InstallerModule.base', '<strong>Welcome</strong> to HumHub<br>Your Social Network Toolbox'); ?></p>

        <p><?php echo Yii::t('InstallerModule.base', 'This wizard will install and configure your own HumHub instance.<br><br>To continue, click Next.'); ?></p>
        <br>
        <hr>
        <div class="row">
            <br/>
            <?php echo HHtml::link(Yii::t('InstallerModule.base', "Next") . ' <i class="fa fa-arrow-circle-right"></i>', array('go'), array('class' => 'btn btn-lg btn-primary')); ?>
            <br/>
            <br/>
        </div>
    </div>


</div>


<div class="text text-center animated fadeIn">

    <?php echo Yii::t('base', "Choose language:"); ?> &nbsp;
    <div class="langSwitcher">
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'choose-language-form',
            'htmlOptions' => array(
                'class' => 'form-inline',
            ),
            'enableAjaxValidation' => false
        ));
        ?>
        <?php echo $form->dropDownList($model, 'language', Yii::app()->params['availableLanguages'], array('submit' => '')); ?>
        <?php $this->endWidget(); ?>
    </div>
</div>