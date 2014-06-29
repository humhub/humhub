<div class="panel panel-default">
    <div class="panel-body">
        <p class="lead"><?php echo Yii::t('InstallerModule.base', '<strong>HumHub</strong> database configuration'); ?></p>

        <p>
            <?php echo Yii::t('InstallerModule.base', 'Below you have to enter your database connection details. If you’re not sure about these, please contact your administrator or web host.'); ?>
        </p>


        <?php $form = $this->beginWidget('CActiveForm', array(
            'id' => 'database-form',
            'enableAjaxValidation' => false,

        )); ?>

        <?php //echo $form->errorSummary($model); ?>
        <hr/>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'hostname'); ?>
            <?php echo $form->textField($model, 'hostname', array('class' => 'form-control', 'id' => 'hostname')); ?>
            <p class="help-block"><?php echo Yii::t('InstallerModule.base', 'You should be able to get this info from your web host, if localhost does not work.'); ?></p>
            <?php echo $form->error($model, 'hostname'); ?>
        </div>
        <hr/>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'username'); ?>
            <?php echo $form->textField($model, 'username', array('class' => 'form-control')); ?>
            <p class="help-block"><?php echo Yii::t('InstallerModule.base', 'Your MySQL username'); ?></p>
            <?php echo $form->error($model, 'username'); ?>
        </div>
        <hr/>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'password'); ?>
            <?php echo $form->passwordField($model, 'password', array('class' => 'form-control')); ?>
            <p class="help-block"><?php echo Yii::t('InstallerModule.base', 'Your MySQL password.'); ?></p>
            <?php echo $form->error($model, 'password'); ?>
        </div>
        <hr/>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'database'); ?>
            <?php echo $form->textField($model, 'database', array('class' => 'form-control')); ?>
            <p class="help-block"><?php echo Yii::t('InstallerModule.base', 'The name of the database you want to run HumHub in.'); ?></p>
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


        <?php echo CHtml::submitButton(Yii::t('InstallerModule.base', 'Check and save'), array('class' => 'btn btn-success')); ?>


        <?php if ($submitted) {
            if ($success) {
                echo HHtml::link(Yii::t('InstallerModule.base', 'Next <i class="fa fa-arrow-circle-right"></i>'), array('//installer/setup/init'), array('class' => 'btn btn-primary'));
            }
        }
        ?>

        <?php $this->endWidget(); ?>
    </div>
</div>