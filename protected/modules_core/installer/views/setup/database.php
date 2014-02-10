<div class="panel panel-default">
    <div class="panel-body">
        <h3> HumHub Installation - Step (2 / 3) </h3>
        <hr>
        <h4>Setup Database Connection</h4><br>


        <?php $form = $this->beginWidget('CActiveForm', array(
            'id' => 'database-form',
            'enableAjaxValidation' => false,

        )); ?>

        <?php //echo $form->errorSummary($model); ?>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'hostname'); ?>
            <?php echo $form->textField($model, 'hostname', array('class' => 'form-control')); ?>
            <?php echo $form->error($model, 'hostname'); ?>
        </div>

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
            <?php echo $form->labelEx($model, 'database'); ?>
            <?php echo $form->textField($model, 'database', array('class' => 'form-control')); ?>
            <?php echo $form->error($model, 'database'); ?>
        </div>

        <?php if ($submitted) { ?>
            <?php if ($success) { ?>
                <div class="alert alert-success">
                    <?php echo Yii::t('InstallerModule.base', 'Yes, database connection works!'); ?>
                </div>
            <?php } else { ?>
                <div class="alert alert-danger">
                    <strong><?php echo Yii::t('InstallerModule.base', 'Ohh, something went wrong!'); ?></strong><br />
                    <?php echo $errorMessage; ?>
                </div>
            <?php } ?>
        <?php } ?>


        <hr>


        <?php echo CHtml::submitButton(Yii::t('InstallerModule.base', 'Check and save'), array('class' => 'btn btn-primary')); ?>


        <?php if ($submitted) {
            if ($success) {
                echo HHtml::link(Yii::t('InstallerModule.base', 'Next <i class="icon-circle-arrow-right"></i>'), array('//installer/setup/init'), array('class' => 'btn btn-success'));
            }
        }
        ?>

        <?php $this->endWidget(); ?>
    </div>
</div>


