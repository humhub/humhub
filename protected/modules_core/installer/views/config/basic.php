<div id="name-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.views_config_basic', 'Social Network <strong>Name</strong>'); ?>
    </div>

    <div class="panel-body">

        <p><?php echo Yii::t('InstallerModule.views_config_basic', 'Of course, your new social network needs a name. Please change the default name with one you like. (For example the name of your company, organization or club)'); ?></p>

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

        <?php echo CHtml::submitButton(Yii::t('InstallerModule.views_config_basic', 'Next'), array('class' => 'btn btn-primary')); ?>

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


