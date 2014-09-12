<div id="database-form" class="panel panel-default animated fadeIn">

    <div class="install-header install-header-small" style="background-image: url('<?php echo $this->module->assetsUrl; ?>/humhub-install-header.jpg');">
        <h2 class="install-header-title"><?php echo Yii::t('InstallerModule.base', '<strong>Database</strong> Configuration'); ?></h2>
    </div>

    <div class="panel-body">
        <p>
            <?php echo Yii::t('InstallerModule.base', 'Below you have to enter your database connection details. If you’re not sure about these, please contact your system administrator.'); ?>
        </p>


        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'database-form',
            'enableAjaxValidation' => false,
        ));
        ?>

<?php //echo $form->errorSummary($model);  ?>
        <hr/>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'hostname'); ?>
            <?php echo $form->textField($model, 'hostname', array('class' => 'form-control', 'id' => 'hostname')); ?>
            <p class="help-block"><?php echo Yii::t('InstallerModule.base', 'Hostname of your MySQL Database Server (e.g. localhost if MySQL is running on the same machine)'); ?></p>
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
                <?php echo HHtml::encode($errorMessage); ?>
                </div>
            <?php } ?>
<?php } ?>


        <hr>

        <?php echo CHtml::submitButton(Yii::t('InstallerModule.base', 'Next'), array('class' => 'btn btn-primary')); ?>

<?php $this->endWidget(); ?>
    </div>
</div>

<script type="text/javascript">

    $(function() {
        // set cursor to email field
        $('#hostname').focus();
    })

    // Shake panel after wrong validation
<?php if ($form->errorSummary($model) != null) { ?>
        $('#database-form').removeClass('fadeIn');
        $('#database-form').addClass('shake');
<?php } ?>

</script>