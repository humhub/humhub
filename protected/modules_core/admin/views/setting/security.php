<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('AdminModule.base', '<strong>Security</strong> settings and roles'); ?></div>
    <div class="panel-body">

        <?php $form = $this->beginWidget('CActiveForm', array(
            'id' => 'security-settings-form',
            'enableAjaxValidation' => false,
        )); ?>

        <?php echo $form->errorSummary($model); ?>


        <?php echo $form->checkBox($model, 'canAdminAlwaysDeleteContent'); ?>

        <?php echo CHtml::submitButton(Yii::t('AdminModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

        <?php $this->endWidget(); ?>

    </div>
</div>








