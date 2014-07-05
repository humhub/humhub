<div id="name-form" class="panel panel-default animated fadeIn">

    <div class="install-header install-header-small" style="background-image: url('<?php echo $this->module->assetsUrl; ?>/humhub-install-header.jpg');">
        <h2 class="install-header-title"><?php echo Yii::t('InstallerModule.base', 'Social Network <strong>Name</strong>'); ?></h2>
    </div>

    <div class="panel-body">

        <p>Of course, your new social network need a name. Please change the default name with one you like. (For example the name of your company, organization or club)</p>

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'basic-form',
            'enableAjaxValidation' => false,
        ));
        ?>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'name'); ?>
            <?php echo $form->textField($model, 'name', array('class' => 'form-control')); ?>
            <?php echo $form->error($model, 'name'); ?>
        </div>

        <hr>

        <?php echo CHtml::submitButton(Yii::t('InstallerModule.base', 'Next'), array('class' => 'btn btn-primary')); ?>

        <?php $this->endWidget(); ?>
    </div>
</div>

<script type="text/javascript">

    $(function () {
        // set cursor to email field
        $('#ConfigBasicForm_name').focus();
    })

    // Shake panel after wrong validation
    <?php if ($form->errorSummary($model) != null) { ?>
    $('#name-form').removeClass('fadeIn');
    $('#name-form').addClass('shake');
    <?php } ?>

</script>


