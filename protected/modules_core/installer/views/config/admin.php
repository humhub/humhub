<div class="panel panel-default">
    <div class="panel-body">
        <h3> HumHub Configuration</h3>
        <hr>
        <h4>Setup Administrator Account</h4><br>
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'admin-form',
            'enableAjaxValidation' => false,
        ));
        ?>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'username'); ?>
            <?php echo $form->textField($model, 'username', array('class' => 'form-control')); ?>
            <?php echo $form->error($model, 'username'); ?>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'password'); ?>
            <?php echo $form->passwordField($model, 'password', array('class' => 'form-control')); ?>
            <?php echo $form->error($model, 'password'); ?>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'passwordVerify'); ?>
            <?php echo $form->passwordField($model, 'passwordVerify', array('class' => 'form-control')); ?>
            <?php echo $form->error($model, 'passwordVerify'); ?>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'email'); ?>
            <?php echo $form->textField($model, 'email', array('class' => 'form-control')); ?>
            <?php echo $form->error($model, 'email'); ?>
        </div>        

        <hr>

        <?php echo CHtml::submitButton(Yii::t('InstallerModule.base', 'Next'), array('class' => 'btn btn-success')); ?>

        <?php $this->endWidget(); ?>

    </div>
</div>


