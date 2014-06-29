<div class="panel panel-default">
    <div class="panel-body">
        <p class="lead"><?php echo Yii::t('InstallerModule.base', '<strong>Your</strong> Social Network name'); ?></p>

        <p>Of course, your new social network need a name. Please change the default name with one you like. (For example the name of your company, organization or club)</p>

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'basic-form',
            'enableAjaxValidation' => true,
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


