<div class="panel panel-default">
    <div class="panel-body">
        <h3> HumHub Configuration</h3>
        <hr>
        <h4>Basic Network Settings</h4><br>

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

        <?php echo CHtml::submitButton(Yii::t('InstallerModule.base', 'Next'), array('class' => 'btn btn-success')); ?>

        <?php $this->endWidget(); ?>
    </div>
</div>


