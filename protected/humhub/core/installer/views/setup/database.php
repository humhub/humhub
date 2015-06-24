<div id="database-form" class="panel panel-default animated fadeIn">

    <div class="panel-heading">
        <?php echo Yii::t('InstallerModule.views_setup_database', '<strong>Database</strong> Configuration'); ?>
    </div>

    <div class="panel-body">
        <p>
            <?php echo Yii::t('InstallerModule.views_setup_database', 'Below you have to enter your database connection details. If you’re not sure about these, please contact your system administrator.'); ?>
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
            <p class="help-block"><?php echo Yii::t('InstallerModule.views_setup_database', 'Hostname of your MySQL Database Server (e.g. localhost if MySQL is running on the same machine)'); ?></p>
<?php echo $form->error($model, 'hostname'); ?>
        </div>
        <hr/>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'username'); ?>
            <?php echo $form->textField($model, 'username', array('class' => 'form-control')); ?>
            <p class="help-block"><?php echo Yii::t('InstallerModule.views_setup_database', 'Your MySQL username'); ?></p>
<?php echo $form->error($model, 'username'); ?>
        </div>
        <hr/>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'password'); ?>
            <?php echo $form->passwordField($model, 'password', array('class' => 'form-control')); ?>
            <p class="help-block"><?php echo Yii::t('InstallerModule.views_setup_database', 'Your MySQL password.'); ?></p>
<?php echo $form->error($model, 'password'); ?>
        </div>
        <hr/>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'database'); ?>
            <?php echo $form->textField($model, 'database', array('class' => 'form-control')); ?>
            <p class="help-block"><?php echo Yii::t('InstallerModule.views_setup_database', 'The name of the database you want to run HumHub in.'); ?></p>
<?php echo $form->error($model, 'database'); ?>
        </div>

        <?php if ($submitted) { ?>
                <?php if ($success) { ?>
                <div class="alert alert-success">
                <?php echo Yii::t('InstallerModule.views_setup_database', 'Yes, database connection works!'); ?>
                </div>
    <?php } else { ?>
                <div class="alert alert-danger">
                    <strong><?php echo Yii::t('InstallerModule.views_setup_database', 'Ohh, something went wrong!'); ?></strong><br />
                <?php echo HHtml::encode($errorMessage); ?>
                </div>
            <?php } ?>
<?php } ?>


        <hr>

        <?php echo CHtml::submitButton(Yii::t('InstallerModule.views_setup_database', 'Next'), array('class' => 'btn btn-primary')); ?>

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