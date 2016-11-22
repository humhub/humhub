<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;


?>
<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<p><?php echo Yii::t('AdminModule.views_setting_logs', 
	'Old logs can significantly increase the size of your database while providing little information.')?></p>
<p><?php echo Yii::t('AdminModule.views_setting_logs', 
	'Currently there are {count} records in the database dating from {dating}.',
	['count' => $logsCount, 'dating' => $dating])?></p>
<br />

<?php $form = CActiveForm::begin(); ?>

<?php echo $form->errorSummary($model); ?>

<div class="form-group">
    <?php echo $form->labelEx($model, 'logsDateLimit'); ?>
    <?php echo $form->dropDownList($model, 'logsDateLimit', $limitAgeOptions, array('class' => 'form-control')); ?>
</div>
<hr>

<?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_logs', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

<?php echo \humhub\widgets\DataSaved::widget(); ?>
<?php CActiveForm::end(); ?>

<?php $this->endContent(); ?>
